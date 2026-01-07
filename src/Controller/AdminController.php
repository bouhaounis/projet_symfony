<?php

namespace App\Controller;

use App\Repository\BookingRepository;
use App\Repository\EventRepository;
use App\Repository\PaymentRepository;
use App\Repository\UserRepository;
use App\Service\SecurityTestService;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/dashboard', name: 'app_admin_dashboard')]
    public function dashboard(
        Request $request,
        BookingRepository $bookingRepository,
        PaymentRepository $paymentRepository,
        UserRepository $userRepository
    ): Response {
        $totalBookings = $bookingRepository->count([]);
        $totalPayments = $paymentRepository->count([]);
        $totalUsers = $userRepository->count([]);
        $totalRevenue = $paymentRepository->createQueryBuilder('p')
            ->select('SUM(p.amount)')
            ->where('p.status = :status')
            ->setParameter('status', 'completed')
            ->getQuery()
            ->getSingleScalarResult() ?? 0;

        $recentBookings = $bookingRepository->findBy([], ['createdAt' => 'DESC'], 5);
        $recentPayments = $paymentRepository->findBy([], ['createdAt' => 'DESC'], 5);
        
        // Recherche par email d'utilisateur
        $userSearch = $request->query->get('userSearch', '');
        if ($userSearch) {
            $recentUsers = $userRepository->createQueryBuilder('u')
                ->where('u.email LIKE :search')
                ->setParameter('search', '%' . $userSearch . '%')
                ->orderBy('u.id', 'DESC')
                ->setMaxResults(5)
                ->getQuery()
                ->getResult();
        } else {
            $recentUsers = $userRepository->findBy([], ['id' => 'DESC'], 5);
        }

        return $this->render('admin/dashboard.html.twig', [
            'totalBookings' => $totalBookings,
            'totalPayments' => $totalPayments,
            'totalUsers' => $totalUsers,
            'totalRevenue' => $totalRevenue,
            'recentBookings' => $recentBookings,
            'recentPayments' => $recentPayments,
            'recentUsers' => $recentUsers,
            'userSearch' => $userSearch,
        ]);
    }

    #[Route('/bookings', name: 'app_admin_bookings', methods: ['GET'])]
    public function bookings(
        Request $request,
        PaginatorInterface $paginator,
        BookingRepository $bookingRepository,
        EventRepository $eventRepository,
        UserRepository $userRepository
    ): Response {
        // Récupérer les paramètres de filtres
        $status = $request->query->get('status', '');
        $dateFromParam = $request->query->get('dateFrom', '');
        $dateToParam = $request->query->get('dateTo', '');
        $totalMinParam = $request->query->get('totalMin', '');
        $totalMaxParam = $request->query->get('totalMax', '');
        $eventParam = $request->query->get('event', '');
        $userParam = $request->query->get('user', '');

        // Parse dates
        $dateFrom = null;
        if ($dateFromParam) {
            try {
                $dateFrom = new \DateTime($dateFromParam);
            } catch (\Exception $e) {
                $dateFrom = null;
            }
        }
        
        $dateTo = null;
        if ($dateToParam) {
            try {
                $dateTo = new \DateTime($dateToParam);
                $dateTo->setTime(23, 59, 59);
            } catch (\Exception $e) {
                $dateTo = null;
            }
        }

        // Parse totals
        $totalMin = !empty($totalMinParam) && is_numeric($totalMinParam) ? (float)$totalMinParam : null;
        $totalMax = !empty($totalMaxParam) && is_numeric($totalMaxParam) ? (float)$totalMaxParam : null;
        $eventId = !empty($eventParam) && is_numeric($eventParam) ? (int)$eventParam : null;
        $userId = !empty($userParam) && is_numeric($userParam) ? (int)$userParam : null;

        // Créer la requête avec filtres
        $queryBuilder = $bookingRepository->createQueryBuilderForAdmin(
            $status ?: null,
            $dateFrom,
            $dateTo,
            $totalMin,
            $totalMax,
            $eventId,
            $userId
        );

        // Paginer les résultats
        $bookings = $paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1),
            15 // 15 réservations par page
        );

        // Récupérer les événements et utilisateurs pour les filtres
        $events = $eventRepository->findAll();
        $users = $userRepository->findAll();

        return $this->render('admin/bookings.html.twig', [
            'bookings' => $bookings,
            'events' => $events,
            'users' => $users,
            'currentStatus' => $status,
            'currentDateFrom' => $dateFromParam,
            'currentDateTo' => $dateToParam,
            'currentTotalMin' => $totalMinParam,
            'currentTotalMax' => $totalMaxParam,
            'currentEvent' => $eventId,
            'currentUser' => $userId,
        ]);
    }

    #[Route('/security-test', name: 'app_admin_security_test', methods: ['GET'])]
    public function securityTest(SecurityTestService $securityTestService): Response
    {
        $report = $securityTestService->runSecurityTests();

        return $this->render('admin/security_test.html.twig', [
            'report' => $report,
        ]);
    }
}

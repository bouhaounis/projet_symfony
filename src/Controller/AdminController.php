<?php

namespace App\Controller;

use App\Repository\BookingRepository;
use App\Repository\PaymentRepository;
use App\Repository\UserRepository;
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
}

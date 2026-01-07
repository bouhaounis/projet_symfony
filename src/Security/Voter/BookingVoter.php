<?php

namespace App\Security\Voter;

use App\Entity\Booking;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Voter pour sécuriser l'accès aux bookings
 * Un utilisateur ne peut accéder qu'à ses propres bookings (sauf admin)
 */
class BookingVoter extends Voter
{
    public const VIEW = 'VIEW';
    public const EDIT = 'EDIT';
    public const DELETE = 'DELETE';
    public const CANCEL = 'CANCEL';

    protected function supports(string $attribute, mixed $subject): bool
    {
        // Ce voter ne traite que les objets Booking
        if (!$subject instanceof Booking) {
            return false;
        }

        // Ce voter supporte ces attributs
        return in_array($attribute, [self::VIEW, self::EDIT, self::DELETE, self::CANCEL]);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // Si l'utilisateur n'est pas connecté, refuser l'accès
        if (!$user instanceof UserInterface) {
            return false;
        }

        /** @var Booking $booking */
        $booking = $subject;

        // Les admins ont accès à tout
        if ($user instanceof User && in_array('ROLE_ADMIN', $user->getRoles())) {
            return true;
        }

        // Vérifier que l'utilisateur est le propriétaire du booking
        return $booking->getUser() === $user;
    }
}



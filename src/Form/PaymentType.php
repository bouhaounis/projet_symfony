<?php

namespace App\Form;

use App\Entity\Booking;
use App\Entity\Payment;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;

class PaymentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('amount', NumberType::class, [
                'label' => 'Montant (€)',
                'attr' => [
                    'class' => 'form-control-luxury',
                    'placeholder' => 'Ex: 99.99',
                    'step' => '0.01',
                    'min' => '0.01',
                ],
                'html5' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Le montant est obligatoire']),
                    new Positive(['message' => 'Le montant doit être positif']),
                    new LessThanOrEqual([
                        'value' => 100000,
                        'message' => 'Le montant ne peut pas dépasser {{ compared_value }}€'
                    ])
                ]
            ])
            ->add('methode', ChoiceType::class, [
                'label' => 'Méthode de paiement',
                'choices' => [
                    '💳 Carte Bancaire' => 'credit_card',
                    '💰 PayPal' => 'paypal',
                    '💵 Espèces' => 'cash',
                    '🏦 Virement' => 'transfer'
                ],
                'attr' => [
                    'class' => 'form-control-luxury',
                ],
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => [
                    '⏳ En attente' => 'pending',
                    '✅ Complété' => 'completed',
                    '❌ Échoué' => 'failed',
                    '↩️ Remboursé' => 'refunded'
                ],
                'attr' => [
                    'class' => 'form-control-luxury',
                ]
            ])
            ->add('booking', EntityType::class, [
                'class' => Booking::class,
                'label' => 'Réservation associée',
                'choice_label' => function(Booking $booking) {
                    return sprintf('📋 Réservation #%d - %d billet(s) - %s€',
                        $booking->getId(),
                        $booking->getQuantity(),
                        number_format($booking->getTotal(), 2, ',', ' ')
                    );
                },
                'attr' => [
                    'class' => 'form-control-luxury',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Payment::class,
        ]);
    }
}

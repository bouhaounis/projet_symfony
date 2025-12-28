<?php

namespace App\Form;

use App\Entity\Event;
use App\Entity\Category;
use App\Entity\Venue;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\Url;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de l\'événement',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: Concert de Jazz'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Le nom est obligatoire'])
                ]
            ])
            ->add('image', UrlType::class, [
                'label' => 'URL de l\'image',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'https://example.com/image.jpg'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'L\'URL de l\'image est obligatoire']),
                    new Url(['message' => 'Veuillez entrer une URL valide']),
                    new Callback([
                        'callback' => function ($value, ExecutionContextInterface $context) {
                            if ($value && !preg_match('/^https?:\/\//i', $value)) {
                                $context->buildViolation('L\'URL doit commencer par http:// ou https://')
                                    ->addViolation();
                            }
                        }
                    ])
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 4,
                    'placeholder' => 'Description de l\'événement...'
                ]
            ])
            ->add('dateEvent', DateType::class, [
                'label' => 'Date de l\'événement',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'La date est obligatoire'])
                ]
            ])
            ->add('venue', EntityType::class, [
                'class' => Venue::class,
                'choice_label' => function(Venue $venue) {
                    return $venue->getName() . ' (' . $venue->getCapacity() . ' places)';
                },
                'label' => 'Lieu (Venue)',
                'placeholder' => 'Sélectionner un lieu',
                'attr' => [
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Le lieu est obligatoire'])
                ]
            ])
            ->add('capacity', IntegerType::class, [
                'label' => 'Capacité (optionnel - sera remplacée par la capacité du lieu)',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'min' => 1,
                    'placeholder' => 'Laisser vide pour utiliser la capacité du lieu'
                ],
                'constraints' => [
                    new Positive(['message' => 'La capacité doit être positive'])
                ]
            ])
            ->add('prix', MoneyType::class, [
                'label' => 'Prix',
                'currency' => 'EUR',
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0,
                    'step' => 0.01
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Le prix est obligatoire']),
                    new Positive(['message' => 'Le prix doit être positif'])
                ]
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'label' => 'Catégorie',
                'placeholder' => 'Sélectionner une catégorie',
                'required' => false,
                'attr' => [
                    'class' => 'form-control'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}

<?php

namespace App\Form;

use App\Entity\Member;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MemberForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom',
                'required' => true,
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'required' => false,
            ])
            ->add('city', TextType::class, [
                'label' => 'Ville',
                'required' => false,
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => [
                    'Actif' => 'actif',
                    'Inactif' => 'inactif',
                    'Suspendu' => 'suspendu',
                ],
                'placeholder' => 'Choisir un statut',
                'required' => false,
            ])
            ->add('promotionYear', IntegerType::class, [
                'label' => 'Année de Promotion',
                'required' => false,
            ])
            ->add('duesPaid', CheckboxType::class, [
                'label' => 'Cotisation Payée',
                'required' => false,
            ])
            ->add('annualPayments', CollectionType::class, [
                'label' => 'Cotisations Annuelles',
                'entry_type' => NumberType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'required' => false,
            ])
            ->add('votingCode', TextType::class, [
                'required' => false,
                'label' => 'Code de vote',
                'row_attr' => ['class' => 'voting-code-row'], // Add a class to target in Twig
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Member::class,
        ]);
    }
}

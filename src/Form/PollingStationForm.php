<?php

namespace App\Form;

use App\Entity\PollingStation;
use App\Enum\Position;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PollingStationForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('position', EnumType::class, [
                'class' => Position::class,
                'choice_label' => fn(Position $pos) => $pos->value,
                'label' => 'Position',
            ])
            ->add('isOpen')
            ->add('isResultPublished')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PollingStation::class,
        ]);
    }
}

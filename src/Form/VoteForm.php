<?php


namespace App\Form;

use App\Entity\Candidate;
use App\Enum\Position;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class VoteForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        foreach (Position::cases() as $position) {
            $builder->add($position->name, EntityType::class, [
                'class' => Candidate::class,
                'query_builder' => fn(EntityRepository $er) =>
                $er->createQueryBuilder('c')
                    ->where('c.position = :pos')
                    ->setParameter('pos', $position),
                'choice_label' => 'name',
                'label' => $position->value, // this is for display only
                'required' => true,
                'expanded' => true, // for radio buttons
            ]);
        }
    }
}

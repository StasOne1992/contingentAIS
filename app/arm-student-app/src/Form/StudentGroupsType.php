<?php

namespace App\Form;

use App\Entity\StudentGroups;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Staff;
use App\Entity\Faculty;

class StudentGroupsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('Name')
            ->add('Letter')
            ->add('faculty',EntityType::class, array(
        'label' => 'Направление подготовки',
        'placeholder' => 'Укажите направление подготовки',
        'empty_data' => null,
        'required'   => false,
        'class' => Faculty::class))
            ->add('code')
            ->add('groupLeader', EntityType::class, array(
        'label' => 'Классный руководитель',
        'placeholder' => 'Укажите классного руководителя',
        'empty_data' => null,
        'required'   => false,
        'class' => Staff::class))
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => StudentGroups::class,
        ]);
    }
}

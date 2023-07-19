<?php

namespace App\Form;

use App\Entity\ContingentDocument;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class ContingentDocumentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('number')
            ->add('createDate', DateType::class, ['widget' => 'single_text', 'format' => 'yyyy-MM-dd'])
            ->add('type')
            ->add('student')
            ->add('name')
            ->add('reason')
            ->add('college')
            ->add('isActive', CheckboxType::class, [
                'label' => 'Приказ активен',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input',
                    'type' => 'checkbox',
                    'required' => false,
                ]]);

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ContingentDocument::class,
        ]);
    }
}

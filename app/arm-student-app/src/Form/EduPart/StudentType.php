<?php

namespace App\Form\EduPart;

use App\Entity\FamilyTypeList;
use App\Entity\HealthGroup;
use App\Entity\Student;
use App\Entity\StudentGroups;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;


class StudentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('NumberZachetka')
            ->add('NumberStudBilet')
            ->add('BirthData', DateType::class, ['widget' => 'single_text', 'format' => 'yyyy-MM-dd'])
            ->add('PhoneNumber')
            ->add('email')
            ->add('FirstName')
            ->add('LastName')
            ->add('MiddleName')
            ->add('DocumentSnils')
            ->add('DocumentMedicalID')
            ->add('AddressFact')
            ->add('AddressMain')
            ->add('PasportSeries')
            ->add('PasportNumber')
            ->add('PasportDate', DateType::class, ['widget' => 'single_text', 'format' => 'yyyy-MM-dd'])
            ->add('PasportIssueOrgan')
            ->add('FamilyTypeID', EntityType::class, array(
                'label' => 'Тип семьи',
                'placeholder' => 'Укажите тип семьи',
                'empty_data' => null,
                'required' => false,
                'class' => FamilyTypeList::class))
            ->add('HealtgGroupID', EntityType::class, array(
                'label' => 'Группа здоровья',
                'placeholder' => 'Укажите группу здоровья',
                'empty_data' => null,
                'required' => false,
                'class' => HealthGroup::class))
            ->add('Sex')
            ->add('isActive', CheckboxType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input',
                    'required' => false,
                ],
            ])
            ->add('studentGroup', EntityType::class, array(
                'label' => 'Учебная группа',
                'placeholder' => 'Укажите учебную группу',
                'empty_data' => null,
                'required' => false,
                'class' => StudentGroups::class))
            ->add('IsOrphan', CheckboxType::class, [
                'label' => 'Сирота',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input',
                    'type' => 'checkbox',
                    'required' => false,
                ],
            ])
            ->add('IsPaid', CheckboxType::class, [
                'label' => 'Платно',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input',
                    'required' => false,
                ],
            ])
            ->add('IsInvalid', CheckboxType::class, [
                'label' => 'Инвалид',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input',
                    'required' => false,
                ],
            ])
            ->add('IsPoor', CheckboxType::class, [
                'label' => 'Малоимущая семья',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input',
                    'required' => false,
                ],
            ])
            ->add('isLiveStudentAccommondation', CheckboxType::class, [
                'label' => 'Проживает в общежитии',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input',
                    'required' => false,
                ],
            ])
        ;


    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Student::class,
        ]);
    }
}

<?php

namespace App\Form;

use App\Entity\Applicant;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ApplicationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('Name', null, [
                'label' => 'Name'
            ])
            ->add('Email', null, [
                'label' => 'Email'
            ])
            ->add('birthdate', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Birthdate'])
            ->add('SAVE', SubmitType::class, [
                'label' => 'SAVE' ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Applicant::class,
        ]);
    }
}


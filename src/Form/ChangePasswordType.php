<?php

namespace App\Form;

use App\Entity\Users;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChangePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('plainPassword', RepeatedType::class, [
                'type'              => PasswordType::class,
                'mapped'            => false,
                'first_options'     => array('label' => 'Nueva Contraseña', 'attr' =>['class' => 'form-control']),
                'second_options'    => array('label' => 'Confirmar Contraseña', 'attr' =>['class' => 'form-control']),
                'invalid_message' => 'Los campos deben ser idénticos.',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Users::class,
        ]);
    }
}

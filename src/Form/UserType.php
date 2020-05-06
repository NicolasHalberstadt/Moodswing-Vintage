<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstname')
            ->add('lastname')
            ->add('email')
            ->add('plain_password', RepeatedType::class, [
                'mapped' => false,
                'type' => PasswordType::class,
                'invalid_message' => 'The password fields must match.',
                'required' => true,
                'first_options'  => ['label' => 'Password'],
                'second_options' => ['label' => 'Repeat Password'],
                'constraints' => [
                    new NotBlank([
                        'message' => "The password should not be blank"
                    ]),
                    new Length([
                        'min' => 6,
                        'max' => 128,
                        'minMessage' => "Your password must be at least {{ limit }} characters long",
                        'maxMessage' => "Your password  cannot be longer than {{ limit }} characters"
                    ]),
                    new Regex([
                        "pattern" => "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[A-Za-z\d\W]{6,}$/",
                        'message' => "Your password must contain at least 6 characters including 1 upper case, 1 lower case and 1 number"
                    ])
                ]

            ])
            ->add('legal_notice', CheckboxType::class, [
                'label' => 'I acknowledge having read and understood the T & Cs and I accept them',
                'required' => true,
                'mapped' => false,
            ])
            ->add('signup', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}

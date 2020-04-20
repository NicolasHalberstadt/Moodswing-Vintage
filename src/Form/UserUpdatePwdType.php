<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserUpdatePwdType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'actual_password',
                PasswordType::class,
                [
                    "mapped" => false,
                ]
            )
            ->add('plain_password', RepeatedType::class, [
                'mapped' => false,
                'type' => PasswordType::class,
                'invalid_message' => 'The password fields must match.',
                'required' => true,
                'first_options'  => ['label' => 'New password'],
                'second_options' => ['label' => 'Password confirmation'],
                'constraints' => [
                    new NotBlank([
                        'message' => "The password should not be blank"
                    ]),
                    new Length([
                        'min' => 6,
                        'max' => 128,
                        'minMessage' => "Your password must be at least {{ limit }} characters long",
                        'maxMessage' => "Your password  cannot be longer than {{ limit }} characters"
                    ])
                ]
            ])
            ->add('save', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}

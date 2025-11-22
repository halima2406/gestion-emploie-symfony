<?php
// src/Form/LoginFormType.php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;

class LoginFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('_username', EmailType::class, [
                'label'  => 'Email',
                'mapped' => false,
            ])
            ->add('_password', PasswordType::class, [
                'label'  => 'Mot de passe',
                'mapped' => false,
            ]);
    }

    // évite le préfixe "login_form[...]" dans les name=""
    public function getBlockPrefix(): string
    {
        return '';
    }
}
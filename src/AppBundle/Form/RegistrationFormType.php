<?php

namespace AppBundle\Form;

use FOS\UserBundle\Util\LegacyFormHelper;

class RegistrationFormType extends \FOS\UserBundle\Form\Type\RegistrationFormType {

    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', null, array('label' =>false, 'attr'=>['placeholder'=>'Benutzername'], 'translation_domain' => 'FOSUserBundle'))
            ->add('plainPassword', LegacyFormHelper::getType('Symfony\Component\Form\Extension\Core\Type\RepeatedType'), array(
                'type' => LegacyFormHelper::getType('Symfony\Component\Form\Extension\Core\Type\PasswordType'),
                'options' => array('translation_domain' => 'FOSUserBundle'),
                'first_options' => array('label' =>false, 'attr'=>['placeholder'=>'Passwort']),
                'second_options' => array('label' =>false, 'attr'=>['placeholder'=>'Passwort wiederholen']),
                'invalid_message' => 'fos_user.password.mismatch',
            ))
        ;
    }

    public function getName()
    {
        return 'acme_user_registration';
    }
}
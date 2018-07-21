<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;

class SettingsFormType extends AbstractType {

    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('telegramUsername', null, array('label' =>'Telegram Nickname','required'=>false))

            ;
        ;
    }

    public function getName()
    {
        return 'settings_form';
    }
}
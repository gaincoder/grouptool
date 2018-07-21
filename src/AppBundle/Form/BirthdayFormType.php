<?php

namespace AppBundle\Form;

use FOS\UserBundle\Util\LegacyFormHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class BirthdayFormType extends AbstractType {

    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('birthdate', BirthdayType::class, array('label' =>false,'widget' => 'single_text', 'attr'=>['placeholder'=>'Geburtstag'],'choice_translation_domain'=>true))
            ->add('name', null, array('label' =>false, 'attr'=>['placeholder'=>'Name']))
        ;
    }

    public function getName()
    {
        return 'app_birthda';
    }
}
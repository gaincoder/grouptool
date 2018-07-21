<?php

namespace AppBundle\Form;

use FOS\UserBundle\Util\LegacyFormHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

class EventFormType extends AbstractType {

    protected $checker;

    public function __construct(AuthorizationChecker $checker)
    {
        $this->checker = $checker;
    }

    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('date', DateTimeType::class, array('label' =>false,'required'=>true,'date_widget' => 'single_text',
                'attr'=>['placeholder'=>'Datum'],'choice_translation_domain'=>true))
            ->add('name', null, array('label' =>false,'required'=>true, 'attr'=>['placeholder'=>'Name']))
            ->add('location', null, array('label' =>false,'required'=>false, 'attr'=>['placeholder'=>'Ort']));

        if($this->checker->isGranted('ROLE_STAMMI')) {
            $builder
                ->add('permission', ChoiceType::class, [
                    'label' => 'Sichtbar für',
                    'choices' => [
                        'Alle' => 0,
                        'Stamm-Mitglieder' => 1
                    ]
                ]);
        }
        $builder
            ->add('info', TextareaType::class, array('label' =>false,'required'=>false,'attr'=>['placeholder'=>'Info','rows'=>15]))
        ;
    }

    public function getName()
    {
        return 'app_event';
    }
}
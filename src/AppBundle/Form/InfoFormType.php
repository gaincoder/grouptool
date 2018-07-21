<?php

namespace AppBundle\Form;

use FOS\UserBundle\Util\LegacyFormHelper;
use Symfony\Component\DomCrawler\Field\TextareaFormField;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

class InfoFormType extends AbstractType {

    protected $checker;

    public function __construct(AuthorizationChecker $checker)
    {
        $this->checker = $checker;
    }


    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('headline', null, array('label' =>false, 'attr'=>['placeholder'=>'Überschrift']));

        if($this->checker->isGranted('ROLE_STAMMI')) {
            $builder
                ->add('permission', ChoiceType::class, [
                    'label' => 'Sichtbar für',
                    'choices' => [
                        'Alle' => 0,
                        'Stamm-Mitglieder' => 1
                    ]
                ]);
            $builder
                ->add('important', CheckboxType::class, array('label' =>'anpinnen','required'=>false));
        }
        $builder
            ->add('text', TextareaType::class, array('label' =>false, 'attr'=>['placeholder'=>'Text','rows'=>15]))
        ;
    }

    public function getName()
    {
        return 'app_info';
    }
}
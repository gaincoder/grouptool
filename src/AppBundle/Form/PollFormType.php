<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

class PollFormType extends AbstractType
{
    protected $checker;

    public function __construct(AuthorizationChecker $checker)
    {
        $this->checker = $checker;
    }


    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name',null, [
                'label' => 'Frage'
            ])
            ->add('endDate',DateType::class,[
                'label' => 'Teilnahme möglich bis',
                'widget' => 'single_text'
            ])
            ->add('type',ChoiceType::class,[
                'label' => 'Typ',
                'choices' => [
                    'Eine Antwort pro Mitglied' => 0,
                    'Mitglieder dürfen mehere Antworten auswählen' => 1
                ]
            ])
            ->add('allowAdd',null,[
                'label' => 'Andere Mitglieder dürfen neue Antwortmöglichkeiten hinzufügen'
            ]);

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
            ->add('info', TextareaType::class, array('label' =>false,'required'=>false,'attr'=>['class'=>'summernote','placeholder'=>'Info','rows'=>15]))
        ;
        $builder
            ->add('answers',CollectionType::class,[
                'entry_type' => PollAnswerFormType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ]);

    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Poll'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_poll';
    }


}

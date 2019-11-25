<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

use App\Util\Util;
use App\Entity\AdvertPlanFile;

/**
 * Description of AdvertisePlanType
 *
 * @author zaealan
 */
class AdvertisePlanType extends AbstractType {

    private $numberOfClients = 2;
    
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        
        $this->numberOfClients = $options['clientsNumber'];
        
//        $this->status = $options['selected_choice'];
//        
//        $choices = ['' => 'File Type',
//            AdvertPlanFile::ADVERT_MAIN_IMAGE => 'Main Image',
//            AdvertPlanFile::ADVERT_BACKGROUND_IMAGE => 'Background Image',
//            AdvertPlanFile::ADVERT_ICON_IMAGE => 'Icon Image'
//        ];
//        
//        $choices = Util::choiceFlip($choices);
//        
//        $this->seconds = $options['selected_seconds_choice'];
//        
//        $choicesTimeInSeconds = ['' => 'Time In Seconds',
//            15 => '15 Seconds',
//            30 => '30 Seconds',
//        ];
//        
//        $choicesTimeInSeconds = Util::choiceFlip($choicesTimeInSeconds);
        
        $builder
                ->add('name', TextType::class, [
                    'required' => true
                ])
                ->add('description', TextareaType::class, [
                    'required' => false
                ])
                ->add('startingDate', DateTimeType::class, [
                    'widget' => 'single_text',
                    'html5' => false,
                    'attr' => [
                        'placeholder' => date('d/m/y H:i'),
                        'class' => 'js-datepicker'
                    ],
                    'format' => 'dd/MM/yyyy H:i',
                    'label' => 'Ending Date',
                    'required' => false,
                    'mapped' => false
                ])
                ->add('endingDate', DateTimeType::class, [
                    'widget' => 'single_text',
                    'html5' => false,
                    'attr' => [
                        'placeholder' => date('d/m/y H:i'),
                        'class' => 'js-datepicker'
                    ],
                    'format' => 'dd/MM/yyyy H:i',
                    'label' => 'Ending Date',
                    'required' => false,
                    'mapped' => false
                ])
        ;
        
//        for ($i = $this->numberOfClients; $i > 0; --$i) {
//            $builder->add('fileName'.$i, FileType::class, [
//                    'label' => 'Advertise Plan File',
//                    // unmapped means that this field is not associated to any entity property
//                    'mapped' => false,
//                    // make it optional so you don't have to re-upload the PDF file
//                    // everytime you edit the Product details
//                    'required' => true
//                    // unmapped fields can't define their validation using annotations
//                    // in the associated entity, so you can use the PHP constraint classes
////                    'constraints' => [
////                        new File([
////                            'maxSize' => '4096k',
////                            'mimeTypes' => [
////                                'image/jpeg'
////                            ],
////                            'mimeTypesMessage' => 'Please upload a valid image file',
////                                ])
////                    ],
//                ])
//                ->add('fileType'.$i, ChoiceType::class, [
//                    'required' => false,
//                    'mapped' => false,
//                    'choices' => $choices,
//                    'data' => $this->status,
//                ])
//                ->add('timeDurationInSeconds'.$i, ChoiceType::class, [
//                    'required' => false,
//                    'mapped' => false,
//                    'choices' => $choicesTimeInSeconds,
//                    'data' => $this->seconds,
//                ])
//                ->add('clientEmail'.$i, EmailType::class, [
//                    'required' => true,
//                    'mapped' => false
//                ]);
//        }
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\AdvertisePlan',
            'clientsNumber' => 2,
            'selected_choice' => null,
            'selected_seconds_choice' => null
        ]);
    }

    /**
     * @return string
     */
    public function getBlockPrefix() {
        return 'adspace_sublicense';
    }

}

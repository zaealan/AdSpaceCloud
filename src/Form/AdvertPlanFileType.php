<?php

namespace App\Form;

use App\Util\Util;
use App\Entity\AdvertPlanFile;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

/**
 * Description of AdvertPlanFileType
 *
 * @author aealan
 */
class AdvertPlanFileType extends AbstractType {

    protected $status;
    protected $seconds;
    protected $formNumber = 1;
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        
        /////////// hay que colocarle mas campos aun a esta chirolada nombre, email, duracion en segundos
        
//        dump($options['form_number']);
//        die;
        
        $this->formNumber = $options['form_number'];
        
        $this->status = $options['selected_choice'];
        
        $choices = ['' => 'File Type',
            AdvertPlanFile::ADVERT_MAIN_IMAGE => 'Main Image',
            AdvertPlanFile::ADVERT_BACKGROUND_IMAGE => 'Background Image',
            AdvertPlanFile::ADVERT_ICON_IMAGE => 'Icon Image'
        ];
        
        $choices = Util::choiceFlip($choices);
        
        $this->seconds = $options['selected_seconds_choice'];
        
        $choicesTimeInSeconds = ['' => 'Time In Seconds',
            15 => '15 Seconds',
            30 => '30 Seconds',
        ];
        
        $choicesTimeInSeconds = Util::choiceFlip($choicesTimeInSeconds);
        
        $builder
                ->add('fileName', FileType::class, [
                    'label' => 'Advertise Plan File',
                    // unmapped means that this field is not associated to any entity property
                    'mapped' => true,
                    // make it optional so you don't have to re-upload the PDF file
                    // everytime you edit the Product details
                    'required' => true
                    // unmapped fields can't define their validation using annotations
                    // in the associated entity, so you can use the PHP constraint classes
//                    'constraints' => [
//                        new File([
//                            'maxSize' => '4096k',
//                            'mimeTypes' => [
//                                'image/jpeg'
//                            ],
//                            'mimeTypesMessage' => 'Please upload a valid image file',
//                                ])
//                    ],
                ])
                ->add('fileType', ChoiceType::class, [
                    'required' => false,
                    'mapped' => true,
                    'choices' => $choices,
                    'data' => $this->status,
                ])
                ->add('timeDurationInSeconds', ChoiceType::class, [
                    'required' => false,
                    'mapped' => true,
                    'choices' => $choicesTimeInSeconds,
                    'data' => $this->seconds,
                ])
                ->add('clientEmail', EmailType::class, [
                    'required' => true,
                    'mapped' => true
                ]);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => AdvertPlanFile::class,
            'selected_choice' => null,
            'selected_seconds_choice' => null,
            'form_number' => null
        ]);
    }
    
    /**
     * @return string
     */
    public function getBlockPrefix() {
//        dump($this->formNumber);
//        die;
        return 'advert_plan_' . $this->formNumber;
    }

}

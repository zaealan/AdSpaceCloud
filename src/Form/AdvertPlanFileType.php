<?php

namespace App\Form;

use App\Entity\AdvertPlanFile;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

/**
 * Description of AdvertPlanFileType
 *
 * @author aealan
 */
class AdvertPlanFileType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('fileName', FileType::class, [
                    'label' => 'Advertise Plan File',
                    // unmapped means that this field is not associated to any entity property
                    'mapped' => true,
                    // make it optional so you don't have to re-upload the PDF file
                    // everytime you edit the Product details
                    'required' => true,
                    // unmapped fields can't define their validation using annotations
                    // in the associated entity, so you can use the PHP constraint classes
                    'constraints' => [
                        new File([
                            'maxSize' => '4096k',
                            'mimeTypes' => [
                                'image/jpeg'
                            ],
                            'mimeTypesMessage' => 'Please upload a valid image file',
                                ])
                    ],
                ]);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => AdvertPlanFile::class,
        ]);
    }

}

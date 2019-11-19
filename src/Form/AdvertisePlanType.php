<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

/**
 * Description of AdvertisePlanType
 *
 * @author zaealan
 */
class AdvertisePlanType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
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
                    'required' => false
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
                    'required' => false
                ])
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\AdvertisePlan'
        ]);
    }

    /**
     * @return string
     */
    public function getBlockPrefix() {
        return 'adspace_sublicense';
    }

}

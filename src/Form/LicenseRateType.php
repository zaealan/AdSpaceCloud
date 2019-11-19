<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class LicenseRateType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('lrPriceServerAndroid', NumberType::class, ['grouping' => true, 'mapped' => false])
                ->add('lrPriceClientAndroid', NumberType::class, ['grouping' => true, 'mapped' => false])
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\LicenseRate'
        ]);
    }

    public function getBlockPrefix() {
        return 'levellicensor_levellicensorbundle_licenserate';
    }

}

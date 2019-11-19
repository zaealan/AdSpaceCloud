<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AccountLicenseType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('alRestaurantName', TextType::class, ['mapped' => true])
                ->add('alLicenseStatus', CheckboxType::class, ['required' => false, 'data' => true])
                ->add('alContacName', TextType::class, ['mapped' => true])
                ->add('alLicenseEmail', EmailType::class, ['mapped' => true])
//                ->add('alAddres', TextType::class, ['mapped' => true])
                ->add('alSuitPoBox', TextType::class, ['required' => false])
                ->add('alPhoneNumber', TextType::class, ['mapped' => true])
                ->add('alAccountLicense', EntityType::class, ['mapped' => true, 'class' => 'App:Account'])
                ->add('isCallCenter', CheckboxType::class, ['required' => false, 'data' => false])
                ->add('isPlusLicense', CheckboxType::class, ['required' => false])
                ->add('hasLevelZero', CheckboxType::class, ['required' => false])
                ->add('isLevelLight', CheckboxType::class, ['required' => false])
                ->add('levelZeroPercentage', TextType::class, ['required' => false])
                ->add('levelZeroGatewayPercentage', TextType::class, ['required' => false])
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\AccountLicense'
        ]);
    }

    /**
     * @return string
     */
    public function getBlockPrefix() {
        return 'levellicensor_levellicensorbundle_accountlicense';
    }

}

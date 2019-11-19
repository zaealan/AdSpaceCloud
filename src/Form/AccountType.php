<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

class AccountType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder
                ->add('acName', TextType::class, [
                    'mapped' => true
                ])
                ->add('acPhoneNumber', TextType::class, ['mapped' => true])
                ->add('acEmail', EmailType::class, ['mapped' => true])
                ->add('acContactName', TextType::class, [
                    'mapped' => true
                ])
                ->add('acSuitPoBox', TextType::class, ['required' => false])
                /*->add('acAddress', TextType::class, [
                    'mapped' => true
                ])*/
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\Account'
        ]);
    }

    /**
     * @return string
     */
    public function getBlockPrefix() {
        return 'levellicensor_levellicensorbundle_account';
    }

}

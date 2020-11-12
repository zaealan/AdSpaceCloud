<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\AccountLicense;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use App\Util\Util;

/**
 * Description of SearchLicenseType
 *
 * @author zaealan
 */
class SearchLicenseType extends AbstractType {

    protected $status;

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {

        $this->status = $options['selected_choice'];

        $choices = ['' => 'All Licenses',
            AccountLicense::LICENSE_STATUS_ACTIVE => 'Activo',
            AccountLicense::LICENSE_STATUS_INACTIVE => 'Inactivo',
            3 => 'With Device',
            4 => 'Without Device',
        ];

        $choices = Util::choiceFlip($choices);

        $builder
                ->add('alContacName', TextType::class, [
                    'required' => false
                ])
                ->add('alLicenseEmail', TextType::class, [
                    'required' => false
                ])
                ->add('alLicenseStatus', ChoiceType::class, [
                    'required' => false,
                    'choices' => $choices,
                    'data' => $this->status,
                ])
                ->add('alRestaurantName', TextType::class, [
                    'required' => false
                ])
                ->add('alLicenseUsername', TextType::class, [
                    'required' => false
                ])
                ->add('deviceUid', TextType::class, [
                    'required' => false
                ])
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\AccountLicense',
            'selected_choice' => null
        ]);
    }

    /**
     * @return string
     */
    public function getBlockPrefix() {
        return 'levellicensor_levellicensorbundle_searchlicense';
    }

}

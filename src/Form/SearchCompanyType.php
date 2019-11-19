<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Company;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use App\Util\Util;

/**
 * Description of SearchCompanyType
 *
 * @author zaealan
 */
class SearchCompanyType extends AbstractType {

    protected $status;

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {

        $this->status = $options['selected_choice'];

        $choices = ['' => 'All Resellers',
            Company::STATUS_ACTIVE => 'Active',
            Company::STATUS_INACTIVE => 'Inactive'
        ];
        
        $choices = Util::choiceFlip($choices);

        $builder
                ->add('coCompanyName', TextType::class, [
                    'required' => false
                ])
                ->add('coCompanyIdentification', TextType::class, [
                    'required' => false
                ])
                ->add('coStatus', ChoiceType::class, [
                    'required' => false,
                    'choices' => $choices,
                    'data' => $this->status,
                ])
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\Company',
            'selected_choice' => null
        ]);
    }

    /**
     * @return string
     */
    public function getBlockPrefix() {
        return 'levellicensor_levellicensorbundle_searchcompany';
    }

}

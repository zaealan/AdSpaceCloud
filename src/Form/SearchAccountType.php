<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Account;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use App\Util\Util;

/**
 * Description of SearchAccountType
 *
 * @author zaealan
 */
class SearchAccountType extends AbstractType {

    protected $status;

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {

        $this->status = $options['selected_choice'];

        $choices = [ '' => 'All Accounts',
            Account::ACCOUNT_STATUS_ACTIVE => 'Active',
            Account::ACCOUNT_STATUS_INACTIVE => 'Inactive'
        ];

        $choices = Util::choiceFlip($choices);

        $builder
                ->add('acName', TextType::class, [
                    'required' => false
                ])
                ->add('acContactName', TextType::class, [
                    'required' => false
                ])
                ->add('acEmail', TextType::class, [
                    'required' => false
                ])
                ->add('deleted', ChoiceType::class, [
                    'required' => false,
                    'choices' => $choices,
                    'data' => $this->status,
                ])
                ->add('alLicenseUsername', TextType::class, [
                    'required' => false,
                    'mapped' => false
                ])
                ->add('deviceUid', TextType::class, [
                    'required' => false,
                    'mapped' => false
                ])
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\Account',
            'selected_choice' => null
        ]);
    }

    /**
     * @return string
     */
    public function getBlockPrefix() {
        return 'levellicensor_levellicensorbundle_searchaccount';
    }

}

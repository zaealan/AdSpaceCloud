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
 * Description of SearchDatabasesType
 * @author aealanzulalecius
 */
class SearchDatabasesType extends AbstractType {

    protected $em;
    protected $status;
    protected $account;

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {

        $this->em = $options['em'];
        $this->status = $options['selected_choice'];
        $this->account = $options['selected_choice_companies'];

        $choices = ['' => 'All Licenses',
            AccountLicense::LICENSE_STATUS_ACTIVE => 'Active',
            AccountLicense::LICENSE_STATUS_INACTIVE => 'Inactive',
            3 => 'With Device',
            4 => 'Without Device',
        ];

        $choices = Util::choiceFlip($choices);

        $choices2 = $this->setSelectCompanies();
        $choices2 = Util::choiceFlip($choices2);

        $builder
                ->add('alLicenseUsername', TextType::class, [
                    'required' => false
                ])
                ->add('dbname', TextType::class, [
                    'required' => false,
                    'mapped' => false
                ])
                ->add('deviceUid', TextType::class, [
                    'required' => false
                ])
                ->add('account', ChoiceType::class, [
                    'required' => false,
                    'mapped' => false,
                    'choices' => $choices2,
                    'data' => $this->account,
                ])
                ->add('alLicenseStatus', ChoiceType::class, [
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
            'data_class' => 'App\Entity\AccountLicense',
            'em' => null,
            'selected_choice' => null,
            'selected_choice_companies' => null
        ]);
    }

    /**
     * @return string
     */
    public function getBlockPrefix() {
        return 'levellicensor_levellicensorbundle_searchdatabase';
    }

    public function setSelectCompanies() {
        $companyRep = $this->em->getRepository('App:Account');

        $companiesAll = $companyRep->findAll();
        $companies = ['' => 'Select Account'];

        foreach ($companiesAll as $comp) {
            $companies[$comp->getId()] = '' . $comp;
        }

        return $companies;
    }

}

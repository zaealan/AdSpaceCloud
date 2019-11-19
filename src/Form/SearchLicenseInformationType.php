<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use App\Util\Util;

/**
 * Description of SearchLicenseInformationType
 * @author aealanzulalecius
 */
class SearchLicenseInformationType extends AbstractType {

    protected $em;
    protected $account;
    protected $apkVersion;
    protected $statusClean;

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {

        $this->em = $options['em'];
        $this->apkVersion = $options['selected_choice'];
        $this->account = $options['selected_choice_companies'];
        $this->statusClean = $options['selected_choice_clean'];

        $choices2 = $this->setSelectCompanies();
        $choices2 = Util::choiceFlip($choices2);

        $choices = $this->setAvailableVersions();
        $choices = Util::choiceFlip($choices);

        $choices3 = ["Filter by cleaning" => "","No" => 0,"Yes" => 1];

        $builder
                ->add('alLicenseUsername', TextType::class, [
                    'required' => false
                ])
                ->add('alContacName', TextType::class, [
                    'required' => false
                ])
                ->add('alLicenseEmail', TextType::class, [
                    'required' => false
                ])
                ->add('alAccountLicense', ChoiceType::class, [
                    'required' => false,
                    'mapped' => false,
                    'choices' => $choices2,
                    'data' => $this->account,
                ])
                ->add('deviceUid', TextType::class, [
                    'required' => false
                ])
                ->add('apkVersion', ChoiceType::class, [
                    'required' => false,
                    'mapped' => false,
                    'choices' => $choices,
                    'data' => $this->apkVersion,
                ])
                ->add('codeInstall', TextType::class, [
                    'required' => false,
                    'mapped' => false
                ])
                ->add('androidLastCleanseLeftDays', ChoiceType::class, [
                    'required' => false,
                    'mapped' => false,
                    'choices' => $choices3,
                    'data' => $this->statusClean,
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
            'selected_choice_companies' => null,
            'selected_choice_clean' => null
        ]);
    }

    /**
     * @return string
     */
    public function getBlockPrefix() {
        return 'levellicensor_levellicensorbundle_searchlicenseinformation';
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

    public function setAvailableVersions() {
        $companyRep = $this->em->getRepository('App:AccountLicense');

        $companiesAll = $companyRep->getDistinctVersionsByLicense();
        $companies = ['' => 'Select Version'];

        foreach ($companiesAll as $comp) {
            $companies[$comp['version']] = $comp['version'];
        }

        return $companies;
    }

}

<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use App\Util\Util;

class UserType extends AbstractType {

    protected $em;
    protected $type;
    protected $company;

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {

        $this->em = $options['em'];

        $this->type = $options['selected_choice_type'];

        $choices = ['' => 'Select..',
            User::USER_ADMINISTRATOR => 'Administrator',
            User::USER_LICENSE_MANAGER => 'License Manager',
            User::USER_REPORT_VIEWER => 'Report Viewer'
        ];

        $choices = Util::choiceFlip($choices);

        $this->company = $options['selected_choice_companies'];

        $choices2 = $this->setSelectCompanies();

        $choices2 = Util::choiceFlip($choices2);

        $builder
                ->add('usName', TextType::class, ['mapped' => true, 'required' => true])
                ->add('usLastName', TextType::class, ['mapped' => true, 'required' => true])
                ->add('usEmail', EmailType::class, ['required' => true])
                ->add('usPhoneNumber', TextType::class, ['mapped' => true, 'required' => true])
                ->add('usStatus', HiddenType::class, [
                    'mapped' => true,
                    'required' => false
                ])
                ->add('usCompany', ChoiceType::class, [
                    'choices' => $choices2,
                    'mapped' => false,
                    'data' => $this->company,
                ])
                ->add('usType', ChoiceType::class, [
                    'choices' => $choices,
                    'data' => $this->type,
                ])
                ->add('username', TextType::class, ['mapped' => true, 'required' => true])
                ->add('password', RepeatedType::class, [
                    'first_options' => ['label' => 'Password', 'attr' => ['maxlength' => 15]],
                    'second_options' => ['label' => 'Confirm Password', 'attr' => ['maxlength' => 15]],
                    'required' => false,
                    'type' => PasswordType::class,
                    'invalid_message' => 'The two passwords must match',
                    'options' => ['label' => 'Password']
                ])
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\User',
            'em' => null,
            'selected_choice_type' => null,
            'selected_choice_companies' => null
        ]);
    }

    /**
     * @return string
     */
    public function getBlockPrefix() {
        return 'levellicensor_levellicensorbundle_user';
    }

    public function setSelectCompanies() {
        $companyRep = $this->em->getRepository('App:Company');

        $companiesAll = $companyRep->findAll();
        $companies = [0 => 'Select..'];

        foreach ($companiesAll as $comp) {
            $companies[$comp->getId()] = '' . $comp;
        }

        return $companies;
    }

}

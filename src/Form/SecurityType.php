<?php

namespace App\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\AbstractType;

/**
 * Description of SecurityType
 * @author aealanzulalecius
 */
class SecurityType extends AbstractType {

    protected $required;

    public function buildForm(FormBuilderInterface $builder, array $options) {

        $this->required = $options['required'];

        $builder
                ->add('secondPass', PasswordType::class, 
                [
                    'mapped' => true,
                    'required' => $this->required,
                    'label' => 'Insert Second Password',
                    'attr' => ['maxlength' => 12]
                ])
        ;
    }

    public function setDefaultOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\User',
            'required' => null
        ]);
    }

    /**
     * @return string
     */
    public function getBlockPrefix() {
        return 'levellicensor_levellicensorbundle_secondpass';
    }

}

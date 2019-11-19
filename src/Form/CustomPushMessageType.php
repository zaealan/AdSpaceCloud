<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

/**
 * Description of CustomPushMessageType
 * @author aealan
 */
class CustomPushMessageType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('messageTitle', TextType::class, ['label' => 'Message Title'])
                ->add('messageBody', TextareaType::class, ['label' => 'Message'])
                ->add('licenses', TextType::class, ['label' => 'Licenses To Update', 'required' => false, 'mapped' => false])
        ;
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\CustomPushMessage',
        ]);
    }

}

<?php

namespace App\Form;

use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

/**
 * Description of PointsAdquiRedemRules
 * @author felipe
 */
class PointsAdquiRedemRules extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('ptsRate', NumberType::class, ['required' => false, 'label' => 'Points Rate'])
            ->add('minimumPurchase', NumberType::class, ['required' => false, 'label' => 'Minimum Purchase'])
            ->add('license', HiddenType::class, ['required' => false, 'label' => 'license']);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\PtsAdquiRedemRules',
        ]);
    }

}

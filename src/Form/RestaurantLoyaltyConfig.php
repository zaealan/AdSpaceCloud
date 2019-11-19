<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

/**
 * Description of RestaurantLoyaltyConfig
 * @author felipe
 */
class RestaurantLoyaltyConfig extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('id', HiddenType::class, ['required' => false, 'label' => 'license'])
            ->add('useLocalPoints', CheckboxType::class, ['required' => false, 'label' => 'Local'])
            ->add('useGlobalPoints', CheckboxType::class, ['required' => false, 'label' => 'Global'])
            ->add('earnPointsInPurchaseWithPoints', CheckboxType::class, ['required' => false, 'label' => 'Give Points?'])
            ->add('minimumPointAmountToExchange', TextType::class, ['required' => false, 'label' => 'Minimum Exchange of Points'])
            ->add('license', HiddenType::class, ['required' => false, 'label' => 'license']);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\RestaurantLoyaltyConfiguration',
        ]);
    }

}

<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

/**
 * Description of ManuallySendPushAPKType
 * @author zaealan
 */
class ManuallySendPushAPKType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('apkName', FileType::class, ['label' => 'APK (.apk file)'])
            ->add('installCode', TextType::class, ['label' => 'Install Code'])
            ->add('versionName', TextType::class, ['label' => 'Version Name'])
            ->add('installAfterDownload', CheckboxType::class, ['label' => 'Install After Download', 'required' => false])
            ->add('licenses', TextType::class, ['label' => 'Licenses To Update', 'required' => false, 'mapped' => false])
        ;
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\ManuallySendPushAPK',
        ]);
    }

}

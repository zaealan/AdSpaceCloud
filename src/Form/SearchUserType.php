<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use App\Util\Util;

/**
 * Description of SearchUserType
 *
 * @author zaealan
 */
class SearchUserType extends AbstractType {

    protected $type;
    protected $status;
    protected $isSuperAdmin;

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {

        $this->isSuperAdmin = $options['is_superadmin'];

        $this->status = $options['selected_choice_status'];

        $choices = ['' => 'Todos Los Usuarios',
            User::STATUS_ACTIVE => 'Activo',
            User::STATUS_INACTIVE => 'Inactivo'
        ];

        $choices = Util::choiceFlip($choices);

        $this->type = $options['selected_choice_type'];

        $choices2 = ['' => 'Todos Los Tipos',
            User::USER_ADMINISTRATOR => 'Administrador',
            User::USER_LICENSE_MANAGER => 'Gestor De Monitores',
            User::USER_ADMIN_DATABASES => 'Supervisor De Publicidad'
        ];

        $choices2 = Util::choiceFlip($choices2);

        $builder
                ->add('usName', TextType::class, [
                    'required' => false
                ])
                ->add('usEmail', TextType::class, [
                    'required' => false
                ])
                ->add('usStatus', ChoiceType::class, [
                    'required' => false,
                    'choices' => $choices,
                    'data' => $this->status,
                ])
        ;
        if ($this->isSuperAdmin) {
            $builder->add('usType', ChoiceType::class, [
                'required' => false,
                'choices' => $choices2,
                'data' => $this->type,
            ]);
        }
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\User',
            'selected_choice_status' => null,
            'selected_choice_type' => null,
            'is_superadmin' => null
        ]);
    }

    /**
     * @return string
     */
    public function getBlockPrefix() {
        return 'levellicensor_levellicensorbundle_searchuser';
    }

}

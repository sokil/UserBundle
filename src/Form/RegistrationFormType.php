<?php

namespace Sokil\UserBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegistrationFormType extends AbstractType
{
    private $class;

    /**
     * @param string $class The User class name
     */
    public function __construct($class)
    {
        $this->class = $class;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', 'email', array('label' => 'form.email', 'translation_domain' => 'FOSUserBundle'))
            ->add('plainPassword', 'repeated', array(
                'type'              => 'password',
                'options'           => array('translation_domain' => 'FOSUserBundle'),
                'first_options'     => array('label' => 'form.password'),
                'second_options'    => array('label' => 'form.password_confirmation'),
                'invalid_message'   => 'fos_user.password.mismatch',
            ))
            ->add('name')
            ->add('phone');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => $this->class,
            'intention'  => 'common',
        ));
    }

    public function getName()
    {
        return 'user_registration_form';
    }
}
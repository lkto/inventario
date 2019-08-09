<?php
/**
 * Created by PhpStorm.
 * User: EBEJARANO
 * Date: 9/08/2019
 * Time: 3:38 PM
 */

namespace App\Form\Type;
use App\Entity\Client;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => false,
                'required'  =>  true
            ])
            ->add('lastName', TextType::class, [
                'label' => false,
                'required'  =>  true
            ])
            ->add('identify', TextType::class, [
                'label' => false,
                'required'  =>  true
            ])
            ->add('enabled', CheckboxType::class, [
                'label' => false,
                'required'  =>  false
            ]);

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Client::class,
        ]);
    }

}
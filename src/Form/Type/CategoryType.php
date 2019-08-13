<?php
/**
 * Created by PhpStorm.
 * User: EBEJARANO
 * Date: 10/08/2019
 * Time: 9:15 AM
 */

namespace App\Form\Type;
use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('code', TextType::class, [
                'label' => false,
                'required'  =>  true
            ])
            ->add('name', TextType::class, [
                'label' => false,
                'required'  =>  true
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Category::class,
        ]);
    }

}
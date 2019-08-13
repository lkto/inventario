<?php
/**
 * Created by PhpStorm.
 * User: EBEJARANO
 * Date: 12/08/2019
 * Time: 11:17 AM
 */

namespace App\Form\Type;
use App\Entity\Category;
use App\Entity\Product;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractType
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
            ])
            ->add('stock', IntegerType::class, [
                'label' => false,
                'required'  =>  true
            ])
            ->add('value', TextType::class, [
                'label' => false,
                'required'  =>  true
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'disabled' => false,
                'label' => 'Categoria'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }

}
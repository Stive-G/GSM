<?php

namespace App\Form\Catalog;

use App\Dto\Catalog\CategoryDto;
use App\Dto\Catalog\ProductDto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var CategoryDto[] $categories */
        $categories = $options['category_choices'];

        $builder
            ->add('name', TextType::class, ['label' => 'Nom'])
            ->add('slug', TextType::class)
            ->add('description', TextareaType::class, ['required' => false])
            ->add('brand', TextType::class, ['required' => false, 'label' => 'Marque'])
            ->add('categories', ChoiceType::class, [
                'multiple' => true,
                'expanded' => false,
                'choices' => $categories,
                'choice_label' => fn (CategoryDto $category) => $category->name,
                'choice_value' => fn (?CategoryDto $category) => $category?->id,
                'label' => 'Catégories',
            ])
            ->add('variants', CollectionType::class, [
                'entry_type' => VariantType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => 'Variantes',
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Actif',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductDto::class,
            'category_choices' => [],
        ]);
    }
}

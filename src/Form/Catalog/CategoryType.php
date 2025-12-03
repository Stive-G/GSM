<?php

namespace App\Form\Catalog;

use App\Dto\Catalog\CategoryDto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var CategoryDto[] $choices */
        $choices = $options['category_choices'];

        $builder
            ->add('name', TextType::class, ['label' => 'Nom'])
            ->add('slug', TextType::class)
            ->add('parentId', ChoiceType::class, [
                'label' => 'Catégorie parente',
                'choices' => $choices,
                'choice_label' => fn (CategoryDto $category) => $category->name,
                'choice_value' => fn (?CategoryDto $category) => $category?->id,
                'required' => false,
                'placeholder' => 'Aucune',
            ])
            ->add('position', IntegerType::class, ['required' => false])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Active',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CategoryDto::class,
            'category_choices' => [],
        ]);
    }
}

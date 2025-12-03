<?php

namespace App\Form\Catalog;

use App\Dto\Catalog\MagasinDto;
use App\Dto\Catalog\StockDto;
use App\Dto\Catalog\VariantDto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var VariantDto[] $variants */
        $variants = $options['variant_choices'];
        /** @var MagasinDto[] $magasins */
        $magasins = $options['magasin_choices'];

        $builder
            ->add('variantId', ChoiceType::class, [
                'label' => 'Variante',
                'choices' => $variants,
                'choice_label' => fn (VariantDto $variant) => $variant->label ?: 'Variante',
                'choice_value' => fn (?VariantDto $variant) => $variant?->id,
                'placeholder' => 'Produit principal',
                'required' => false,
            ])
            ->add('magasinId', ChoiceType::class, [
                'label' => 'Magasin',
                'choices' => $magasins,
                'choice_label' => fn (MagasinDto $magasin) => $magasin->name,
                'choice_value' => fn (?MagasinDto $magasin) => $magasin?->id,
            ])
            ->add('quantity', NumberType::class, ['label' => 'Quantité']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => StockDto::class,
            'variant_choices' => [],
            'magasin_choices' => [],
        ]);
    }
}

<?php

namespace App\Form;

use App\Entity\DocumentLigne;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DocumentLigneType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Id MongoDB du produit (on pourra plus tard mettre un autocomplete)
            ->add('productIdMongo', TextType::class, [
                'label' => 'ID produit Mongo',
            ])
            // Libellé snapshoté
            ->add('productLabel', TextType::class, [
                'label' => 'Libellé produit',
            ])
            ->add('unit', TextType::class, [
                'label' => 'Unité (sac, palette, m², …)',
            ])
            ->add('quantity', NumberType::class, [
                'label' => 'Quantité',
                'scale' => 4,
            ])
            ->add('unitPriceHt', NumberType::class, [
                'label' => 'Prix unitaire HT',
                'scale' => 2,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DocumentLigne::class,
        ]);
    }
}

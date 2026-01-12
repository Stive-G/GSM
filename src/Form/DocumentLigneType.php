<?php

namespace App\Form;

use App\Entity\DocumentLigne;
use App\Entity\ProductRef;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DocumentLigneType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('productRef', EntityType::class, [
                'class' => ProductRef::class,
                'choice_label' => 'label',
                'placeholder' => 'Choisir un produit',
                'required' => true,
                // optionnel : recherche rapide dans EasyAdmin via autocomplete
                // 'attr' => ['data-ea-widget' => 'ea-autocomplete'],
            ])
            ->add('quantity', NumberType::class, [
                'required' => true,
                'scale' => 2,
                'html5' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DocumentLigne::class,
        ]);
    }
}

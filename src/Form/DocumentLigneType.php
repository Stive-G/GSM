<?php
namespace App\Form;

use App\Entity\DocumentLigne;
use App\Entity\Article;
use App\Entity\Conditionnement;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class DocumentLigneType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('article', EntityType::class, [
                'class' => Article::class,
                'choice_label' => 'label',
            ])
            ->add('conditionnement', EntityType::class, [
                'class' => Conditionnement::class,
                'choice_label' => 'label',
            ])
            ->add('designation', TextType::class)
            ->add('quantity', NumberType::class, ['scale' => 4])
            ->add('unitPrice', NumberType::class, ['scale' => 2]);
    }
}

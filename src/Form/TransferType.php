<?php
namespace App\Form;

use App\Dto\TransferRequest;
use App\Entity\Article;
use App\Entity\Conditionnement;
use App\Entity\Magasin;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class TransferType extends AbstractType
{
    public function buildForm(FormBuilderInterface $b, array $options): void
    {
        $b
            ->add('article', EntityType::class, [
                'class' => Article::class,
                'choice_label' => fn(Article $a) => sprintf('%s — %s', $a->getReference(), $a->getLabel()),
                'placeholder' => 'Choisir un article',
            ])
            ->add('conditionnement', EntityType::class, [
                'class' => Conditionnement::class,
                'choice_label' => fn(Conditionnement $c) => $c->__toString(),
                'placeholder' => 'Choisir un conditionnement',
            ])
            ->add('source', EntityType::class, [
                'class' => Magasin::class,
                'choice_label' => 'name',
                'placeholder' => 'Magasin source',
            ])
            ->add('destination', EntityType::class, [
                'class' => Magasin::class,
                'choice_label' => 'name',
                'placeholder' => 'Magasin destination',
            ])
            ->add('quantity', NumberType::class, [
                'scale' => 4,
            ])
            ->add('comment', TextareaType::class, [
                'required' => false,
            ]);
    }
}

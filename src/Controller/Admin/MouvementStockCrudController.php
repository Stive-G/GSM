<?php
namespace App\Controller\Admin;

use App\Entity\MouvementStock;
use App\Service\StockService;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;

class MouvementStockCrudController extends AbstractCrudController
{
    public function __construct(private StockService $stockService) {}

    public static function getEntityFqcn(): string { return MouvementStock::class; }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityPermission('ROLE_MAGASINIER')
            ->setEntityLabelInPlural('Mouvements de stock')
            ->setEntityLabelInSingular('Mouvement de stock');
    }

    public function configureFields(string $pageName): iterable
    {
        yield AssociationField::new('article');
        yield AssociationField::new('magasin');
        yield ChoiceField::new('type', 'Type')->setChoices([
            'Entrée' => MouvementStock::TYPE_IN,
            'Sortie' => MouvementStock::TYPE_OUT,
            'Perte'  => MouvementStock::TYPE_LOSS,
            'Ajustement' => MouvementStock::TYPE_ADJUST,
        ]);
        yield NumberField::new('quantity', 'Quantité')->setNumDecimals(3);
        yield DateTimeField::new('createdAt', 'Créé le')->onlyOnIndex();
        yield TextareaField::new('comment', 'Commentaire')->hideOnIndex();
    }

    public function persistEntity(EntityManagerInterface $em, $entityInstance): void
    {
        if ($entityInstance instanceof MouvementStock) {
            $this->stockService->applyMovement($entityInstance);
        }
        parent::persistEntity($em, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $em, $entityInstance): void
    {
        if ($entityInstance instanceof MouvementStock) {
            // Stratégie simple: on interdit l'édition d'un mouvement existant si c'est un ADJUST,
            // sinon on fait revert + apply (pour gérer le changement de qty/type).
            $uow = $em->getUnitOfWork();
            $original = $uow->getOriginalEntityData($entityInstance);

            /** @var MouvementStock $before */
            $before = (new MouvementStock());
            $before->setArticle($original['article']);
            $before->setMagasin($original['magasin']);
            $before->setType($original['type']);
            $before->setQuantity($original['quantity']);

            if ($before->getType() === MouvementStock::TYPE_ADJUST) {
                throw new \RuntimeException("Edition d'un ajustement non autorisée (crée un nouvel ajustement).");
            }

            $this->stockService->revertMovement($before);
            $this->stockService->applyMovement($entityInstance);
        }
        parent::updateEntity($em, $entityInstance);
    }

    public function deleteEntity(EntityManagerInterface $em, $entityInstance): void
    {
        if ($entityInstance instanceof MouvementStock) {
            if ($entityInstance->getType() === MouvementStock::TYPE_ADJUST) {
                throw new \RuntimeException("Suppression d'un ajustement non autorisée (préférez un nouvel ajustement).");
            }
            $this->stockService->revertMovement($entityInstance);
        }
        parent::deleteEntity($em, $entityInstance);
    }
}

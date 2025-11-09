<?php
namespace App\Controller\Admin;

use App\Entity\Document;
use App\Form\DocumentLigneType;
use App\Service\DocumentService;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;

class DocumentCrudController extends AbstractCrudController
{
    public function __construct(private DocumentService $docService) {}

    public static function getEntityFqcn(): string { return Document::class; }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setEntityPermission('ROLE_VENDEUR')
            ->setEntityLabelInPlural('Documents')
            ->setEntityLabelInSingular('Document');
    }

    public function configureFields(string $pageName): iterable
    {
        yield ChoiceField::new('type')->setChoices([
            'Devis' => Document::TYPE_DEVIS,
            'Vente' => Document::TYPE_VENTE,
        ]);
        yield AssociationField::new('client');
        yield DateTimeField::new('createdAt')->onlyOnIndex();

        yield CollectionField::new('lignes', 'Lignes')
            ->setEntryType(DocumentLigneType::class)
            ->allowAdd()
            ->allowDelete()
            ->renderExpanded(true);
    }

    public function persistEntity(EntityManagerInterface $em, $entityInstance): void
    {
        parent::persistEntity($em, $entityInstance);
        $this->docService->processDocument($entityInstance); // applique stock si VENTE
    }

    public function updateEntity(EntityManagerInterface $em, $entityInstance): void
    {
        parent::updateEntity($em, $entityInstance);
        $this->docService->reprocessDocument($entityInstance); // recalcul simple (re-apply)
    }
}

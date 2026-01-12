<?php

namespace App\Controller\Admin;

use App\Entity\Document;
use App\Form\DocumentLigneType;
use App\Service\DocumentService;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;

class DocumentCrudController extends AbstractCrudController
{
    public function __construct(private DocumentService $documentService) {}

    public static function getEntityFqcn(): string
    {
        return Document::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityPermission('ROLE_VENDEUR')
            ->setEntityLabelInPlural('Documents')
            ->setEntityLabelInSingular('Document');
    }

    public function configureActions(Actions $actions): Actions
    {
        $pdf = Action::new('pdf', 'PDF')
            ->setIcon('fa fa-file-pdf')
            ->linkToUrl(fn(Document $doc) => $this->generateUrl('admin_document_pdf', [
                'id' => $doc->getId(),
            ]))
            ->setHtmlAttributes(['target' => '_blank']);

        return $actions
            ->add(Crud::PAGE_INDEX, $pdf)
            ->add(Crud::PAGE_DETAIL, $pdf);
    }

    public function configureFields(string $pageName): iterable
    {
        yield ChoiceField::new('type')->setChoices([
            'Devis'  => Document::TYPE_DEVIS,
            'Vente'  => Document::TYPE_VENTE,
        ]);

        yield AssociationField::new('client');
        yield DateTimeField::new('createdAt', 'Créé le')
            ->onlyOnIndex()
            ->setFormat('dd/MM/yyyy HH:mm');

        yield CollectionField::new('lignes', 'Lignes')
            ->setEntryType(DocumentLigneType::class)
            ->setFormTypeOptions(['by_reference' => false])
            ->allowAdd()
            ->allowDelete()
            ->renderExpanded(true);
    }

    public function persistEntity(EntityManagerInterface $em, $entityInstance): void
    {
        $this->documentService->prepareDocument($entityInstance);
        parent::persistEntity($em, $entityInstance);
        $this->documentService->processDocument($entityInstance);
    }

    public function updateEntity(EntityManagerInterface $em, $entityInstance): void
    {
        $this->documentService->prepareDocument($entityInstance);
        parent::updateEntity($em, $entityInstance);
        $this->documentService->reprocessDocument($entityInstance);
    }
}

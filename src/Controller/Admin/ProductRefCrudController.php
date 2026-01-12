<?php

namespace App\Controller\Admin;

use App\Entity\ProductRef;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class ProductRefCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ProductRef::class;
    }
}

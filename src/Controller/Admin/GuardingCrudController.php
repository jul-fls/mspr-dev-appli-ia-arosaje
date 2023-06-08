<?php

namespace App\Controller\Admin;

use App\Entity\Guarding;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class GuardingCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Guarding::class;
    }

    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */
}

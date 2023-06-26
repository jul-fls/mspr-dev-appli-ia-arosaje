<?php

namespace App\Controller;

use App\Entity\Role;
use App\Form\RoleType;
use App\Repository\RoleRepository;
use App\Service\RoleChecker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/role')]
class RoleController extends AbstractController
{

    private $roleChecker;

    private $entityManager;
    public function __construct(EntityManagerInterface $entityManager, RoleChecker $roleChecker)
    {
        $this->entityManager = $entityManager;
        $this->roleChecker = $roleChecker;
    }
    
    #[Route('/', name: 'app_role_index', methods: ['GET'])]
    public function index(RoleRepository $roleRepository, Request $request): Response
    {
        try {
            $this->roleChecker->checkUserRole($request->getSession()->get('user'), 'Administrateur');
        } catch (AccessDeniedException $e) {
            return $this->json(['message' => $e->getMessage()], 403);
        }
        return $this->render('role/index.html.twig', [
            'roles' => $roleRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_role_new', methods: ['GET', 'POST'])]
    public function new(Request $request, RoleRepository $roleRepository): Response
    {
        try {
            $this->roleChecker->checkUserRole($request->getSession()->get('user'), 'Administrateur');
        } catch (AccessDeniedException $e) {
            return $this->json(['message' => $e->getMessage()], 403);
        }
        $role = new Role();
        $form = $this->createForm(RoleType::class, $role);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $roleRepository->save($role, true);

            return $this->redirectToRoute('app_role_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('role/new.html.twig', [
            'role' => $role,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_role_show', methods: ['GET'])]
    public function show(Role $role, Request $request): Response
    {
        try {
            $this->roleChecker->checkUserRole($request->getSession()->get('user'), 'Administrateur');
        } catch (AccessDeniedException $e) {
            return $this->json(['message' => $e->getMessage()], 403);
        }
        return $this->render('role/show.html.twig', [
            'role' => $role,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_role_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Role $role, RoleRepository $roleRepository): Response
    {
        try {
            $this->roleChecker->checkUserRole($request->getSession()->get('user'), 'Administrateur');
        } catch (AccessDeniedException $e) {
            return $this->json(['message' => $e->getMessage()], 403);
        }
        $form = $this->createForm(RoleType::class, $role);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $roleRepository->save($role, true);

            return $this->redirectToRoute('app_role_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('role/edit.html.twig', [
            'role' => $role,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_role_delete', methods: ['POST'])]
    public function delete(Request $request, Role $role, RoleRepository $roleRepository): Response
    {
        try {
            $this->roleChecker->checkUserRole($request->getSession()->get('user'), 'Administrateur');
        } catch (AccessDeniedException $e) {
            return $this->json(['message' => $e->getMessage()], 403);
        }
        if ($this->isCsrfTokenValid('delete'.$role->getId(), $request->request->get('_token'))) {
            $roleRepository->remove($role, true);
        }

        return $this->redirectToRoute('app_role_index', [], Response::HTTP_SEE_OTHER);
    }
}

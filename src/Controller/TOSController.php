<?php

namespace App\Controller;

use App\Service\RoleChecker;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class TOSController extends AbstractController
{

    private $roleChecker;
    private $requestStack;
    private $entityManager;
    private $doctrine;
    public function __construct(EntityManagerInterface $entityManager, RoleChecker $roleChecker, RequestStack $requestStack, ManagerRegistry $managerRegistry)
    {
        $this->requestStack = $requestStack;
        $this->entityManager = $entityManager;
        $this->roleChecker = $roleChecker;
        $this->doctrine = $managerRegistry;
    }

    
    #[Route('/tos', name: 'app_tos')]
    public function index(Request $request): Response
    {
        $session = $this->requestStack->getSession();
        $user = $session->get('user');
        $em = $this->doctrine->getManager();

        if (!$user) {
            return $this->redirectToRoute('app_user_login');
        }

        if (!$em->getRepository('App\Entity\User')->find($user->getId())) {
            throw new NotFoundHttpException("User not found");
        }
        
        try {
            $this->roleChecker->checkUserRole($request->getSession()->get('user'), 'Utilisateur');
        } catch (AccessDeniedException $e) {
            return $this->json(['message' => $e->getMessage()], 403);
        }
        return $this->render('tos/index.html.twig', [
            'controller_name' => 'TOSController',
        ]);
    }
}

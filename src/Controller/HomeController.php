<?php

namespace App\Controller;

use App\Service\RoleChecker;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class HomeController extends AbstractController
{
    public function __construct(
        private RequestStack $requestStack,
        private ManagerRegistry $doctrine,
        private RoleChecker $roleChecker
    ) {}

    #[Route('/', name: 'app_home')]
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
        $userplants = $user->getPlants()
            ? $em->getRepository('App\Entity\Plant')->findBy(['owner' => $user->getId()])
            : null;

            $publishedplants = $em->getRepository('App\Entity\Plant')
            ->createQueryBuilder('p')
            ->where('p.is_published = :is_published')
            ->andWhere('p.owner != :owner')
            ->setParameters([
                'is_published' => true,
                'owner' => $user
            ])
            ->getQuery()
            ->getResult();
        

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'user_id' => $user->getId(),
            'userplants' => $userplants,
            'publishedplants' => $publishedplants
        ]);
    }
}

<?php

namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    public function __construct(
        private RequestStack $requestStack,
        private ManagerRegistry $doctrine
    ) {}

    #[Route('/', name: 'app_home')]
    public function index(): Response
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

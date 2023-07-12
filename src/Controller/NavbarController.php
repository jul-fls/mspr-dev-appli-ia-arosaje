<?php
namespace App\Controller;

use App\Service\RoleChecker;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/navbar')]
class NavbarController extends AbstractController
{
    public function __construct(
        private RequestStack $requestStack,
        private ManagerRegistry $doctrine,
        private RoleChecker $roleChecker
    ) {}

    #[Route('/unread-messages', name: 'app_unread_messages')]
    public function unreadMessages(Request $request): Response
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
        // Récupérer les messages non lus
        $unreadMessages = $em->getRepository('App\Entity\Message')
            ->findUnreadMessages($user->getId());

        return $this->render('navbar/unread_messages.html.twig', [
            'unreadMessagesCount' => count($unreadMessages)
        ]);
    }
}

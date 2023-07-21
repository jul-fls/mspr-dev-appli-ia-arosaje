<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Form\ConversationType;
use App\Repository\ConversationRepository;
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

#[Route('/conversation')]
class ConversationController extends AbstractController
{
    private $entityManager;
    private $roleChecker;
    private $doctrine;
    private $requestStack;
    public function __construct(RequestStack $requestStack,ManagerRegistry $doctrine, RoleChecker $roleChecker, EntityManagerInterface $entityManager) {
        $this->requestStack = $requestStack;
        $this->doctrine = $doctrine;
        $this->roleChecker = $roleChecker;
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'app_conversation_index', methods: ['GET'])]
    public function index(Request $request, ConversationRepository $conversationRepository): Response
    {
        try {
            $this->roleChecker->checkUserRole($request->getSession()->get('user'), 'Utilisateur');
        } catch (AccessDeniedException $e) {
            return $this->json(['message' => $e->getMessage()], 403);
        }

        //find all conversations in which the user is the sender or the receiver but don't show if the user is not the sender or the receiver
        $user_conversations = $conversationRepository->findBy(['from_user' => $request->getSession()->get('user')->getId()]);
        $user_conversations2 = $conversationRepository->findBy(['to_user' => $request->getSession()->get('user')->getId()]);
        $conversations = array_merge($user_conversations, $user_conversations2);
        $conversations = array_unique($conversations, SORT_REGULAR);
        return $this->render('conversation/index.html.twig', [
            'conversations' => $conversations,
        ]);
    }

    #[Route('/new', name: 'app_conversation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ConversationRepository $conversationRepository): Response
    {
        try {
            $this->roleChecker->checkUserRole($request->getSession()->get('user'), 'Utilisateur');
        } catch (AccessDeniedException $e) {
            return $this->json(['message' => $e->getMessage()], 403);
        }

        $conversation = new Conversation();
        $form = $this->createForm(ConversationType::class, $conversation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $conversationRepository->save($conversation, true);

            // Récupérer l'identifiant de la conversation précédente
            $previousConversationId = $conversation->getId();

            // Générer la route vers la page de la conversation précédente
            $route = $this->generateUrl('app_conversation_show', ['id' => $previousConversationId]);
            
            return $this->redirect($route);
        }

        return $this->renderForm('conversation/new.html.twig', [
            'conversation' => $conversation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_conversation_show', methods: ['GET'])]
    public function show(Request $request, Conversation $conversation): Response
    {
        try {
            $this->roleChecker->checkUserRole($request->getSession()->get('user'), 'Utilisateur');
            if($conversation->getFromUser()->getId() !== $request->getSession()->get('user')->getId() && $conversation->getToUser()->getId() !== $request->getSession()->get('user')->getId()) {
                throw new AccessDeniedException('Vous n\'avez pas les droits suffisants pour accéder à cette ressource.');
            }
        } catch (AccessDeniedException $e) {
            return $this->json(['message' => $e->getMessage()], 403);
        }
        $session = $this->requestStack->getSession();
        $user = $session->get('user');
        $em = $this->doctrine->getManager();

        if (!$user) {
            return $this->redirectToRoute('app_user_login');
        }
        
        try {
            $this->roleChecker->checkUserRole($session->get('user'), 'Utilisateur');
        } catch (AccessDeniedException $e) {
            return $this->json(['message' => $e->getMessage()], 403);
        }

        $em->getRepository('App\Entity\Message')
            ->markMessagesAsRead($conversation->getId(), $user->getId());
        return $this->render('conversation/conversation.html.twig', [
            'conversation' => $conversation,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_conversation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Conversation $conversation, ConversationRepository $conversationRepository): Response
    {
        try {
            $this->roleChecker->checkUserRole($request->getSession()->get('user'), 'Administrateur');
        } catch (AccessDeniedException $e) {
            return $this->json(['message' => $e->getMessage()], 403);
        }
        $form = $this->createForm(ConversationType::class, $conversation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $conversationRepository->save($conversation, true);

            return $this->redirectToRoute('app_conversation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('conversation/edit.html.twig', [
            'conversation' => $conversation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_conversation_delete', methods: ['POST'])]
    public function delete(Request $request, Conversation $conversation, ConversationRepository $conversationRepository): Response
    {
        try {
            $this->roleChecker->checkUserRole($request->getSession()->get('user'), 'Administrateur');
        } catch (AccessDeniedException $e) {
            return $this->json(['message' => $e->getMessage()], 403);
        }
        if ($this->isCsrfTokenValid('delete'.$conversation->getId(), $request->request->get('_token'))) {
            $conversationRepository->remove($conversation, true);
        }

        return $this->redirectToRoute('app_conversation_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/check-unread-messages/{conversation_id}', name: 'app_check_unread_messages', methods: ['GET'])]
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
            ->findUnreadMessagesForConversation($user->getId(), $request->attributes->get('conversation_id'));

        return $this->json(['unreadMessagesCount' => count($unreadMessages)]);
    }
}

<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\User;
use App\Form\MessageType;
use App\Repository\MessageRepository;
use App\Service\RoleChecker;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Mime\Email;

#[Route('/message')]
class MessageController extends AbstractController
{
    public function __construct(
        private RequestStack $requestStack,
        private ManagerRegistry $doctrine,
        private RoleChecker $roleChecker
    ) {}
    
    #[Route('/', name: 'app_message_index', methods: ['GET'])]
    public function index(Request $request, MessageRepository $messageRepository): Response
    {
        try {
            $this->roleChecker->checkUserRole($request->getSession()->get('user'), 'Administrateur');
        } catch (AccessDeniedException $e) {
            return $this->json(['message' => $e->getMessage()], 403);
        }
        return $this->render('message/index.html.twig', [
            'messages' => $messageRepository->findAll(),
        ]);
    }

    public function sendNewMessageEmail(MailerInterface $mailer, Message $message, User $user_sender, User $other_user)
    {
        $email = (new Email())
            ->from('mailgun@mailgun.flusin.fr')
            ->to($other_user->getEmail())
            ->subject('New message from ' . $user_sender->getFirstName() . ' ' . $user_sender->getLastName())
            ->text("Vous avez reçu un nouveau message de " . $user_sender->getFirstName(). ' ' . $user_sender->getLastName() . " sur la messagerie d'A'rosa-je.")
            ->html($message->getContent());

            try {
                $mailer->send($email);
            } catch (TransportExceptionInterface $e) {
                dump($e->getMessage()); // Display the error message if there was an issue sending the email
            }
    }

    #[Route('/new', name: 'app_message_new', methods: ['GET', 'POST'])]
    public function new(Request $request, MessageRepository $messageRepository, MailerInterface $mailer): Response
    {
        try {
            $this->roleChecker->checkUserRole($request->getSession()->get('user'), 'Utilisateur');
        } catch (AccessDeniedException $e) {
            return $this->json(['message' => $e->getMessage()], 403);
        }

        $message = new Message();
        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $session = $this->requestStack->getSession();
            $user = $session->get('user');
            $em = $this->doctrine->getManager();
            $user_sender = $em->getRepository('App\Entity\User')->find($user->getId());
            $message->setSender($user_sender);
            $message->setSentAt(new \DateTimeImmutable());
            if($message->getConversation()->getMessages()->count() > 0) {
                $message->setReplyToMessage($message->getConversation()->getMessages()->last()->getId());
            }
            try {
                $this->roleChecker->checkUserRole($request->getSession()->get('user'), 'Utilisateur');
                if($form->get('conversation')->getData()->getFromUser()->getId() !== $request->getSession()->get('user')->getId() && $form->get('conversation')->getData()->getToUser()->getId() !== $request->getSession()->get('user')->getId()) {
                    throw new AccessDeniedException('Vous n\'avez pas les droits suffisants pour accéder à cette ressource.');
                }
            } catch (AccessDeniedException $e) {
                return $this->json(['message' => $e->getMessage()], 403);
            }
            $messageRepository->save($message, true);

            //Send email to the other user of the conversation to tell that a new message has been sent
            $other_user = $message->getConversation()->getFromUser()->getId() === $user->getId() ? $message->getConversation()->getToUser() : $message->getConversation()->getFromUser();
            $this->sendNewMessageEmail($mailer, $message, $user_sender, $other_user);
            // Récupérer l'identifiant de la conversation précédente
            $previousConversationId = $message->getConversation()->getId();

            // Générer la route vers la page de la conversation précédente
            $route = $this->generateUrl('app_conversation_show', ['id' => $previousConversationId]);

            return $this->redirect($route);
        }

        return $this->renderForm('message/new.html.twig', [
            'message' => $message,
            'form' => $form,
        ]);
    }

    #[Route('/show/{id}', name: 'app_message_show', methods: ['GET'])]
    public function show(Request $request, Message $message): Response
    {
        try {
            $this->roleChecker->checkUserRole($request->getSession()->get('user'), 'Utilisateur');
            if($message->getSender()->getId() !== $request->getSession()->get('user')->getId()) {
                throw new AccessDeniedException('Vous n\'avez pas les droits suffisants pour accéder à cette ressource.');
            }
        } catch (AccessDeniedException $e) {
            return $this->json(['message' => $e->getMessage()], 403);
        }
        return $this->render('message/show.html.twig', [
            'message' => $message,
        ]);
    }

    #[Route('/edit/{id}', name: 'app_message_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Message $message, MessageRepository $messageRepository): Response
    {
        try {
            $this->roleChecker->checkUserRole($request->getSession()->get('user'), 'Administrateur');
        } catch (AccessDeniedException $e) {
            return $this->json(['message' => $e->getMessage()], 403);
        }
        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $messageRepository->save($message, true);

            return $this->redirectToRoute('app_message_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('message/edit.html.twig', [
            'message' => $message,
            'form' => $form,
        ]);
    }

    #[Route('/delete/{id}', name: 'app_message_delete', methods: ['POST'])]
    public function delete(Request $request, Message $message, MessageRepository $messageRepository): Response
    {
        try {
            $this->roleChecker->checkUserRole($request->getSession()->get('user'), 'Administrateur');
        } catch (AccessDeniedException $e) {
            return $this->json(['message' => $e->getMessage()], 403);
        }
        if ($this->isCsrfTokenValid('delete'.$message->getId(), $request->request->get('_token'))) {
            $messageRepository->remove($message, true);
        }

        return $this->redirectToRoute('app_message_index', [], Response::HTTP_SEE_OTHER);
    }
}

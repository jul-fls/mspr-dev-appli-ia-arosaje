<?php

namespace App\Controller;

use App\Entity\Plant;
use App\Entity\User;
use App\Form\UserType;
use App\Form\UsermodifyType;
use App\Repository\ConversationRepository;
use App\Repository\UserRepository;
use App\Service\RoleChecker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/user')]
class UserController extends AbstractController
{

    private $roleChecker;
    private $entityManager;
    private $conversationRepository;
    public function __construct(EntityManagerInterface $entityManager, RoleChecker $roleChecker, ConversationRepository $conversationRepository)
    {
        $this->entityManager = $entityManager;
        $this->roleChecker = $roleChecker;
        $this->conversationRepository = $conversationRepository;
    }


    #[Route('/', name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository, Request $request): Response
    {
        try {
            $this->roleChecker->checkUserRole($request->getSession()->get('user'), 'Administrateur');
        } catch (AccessDeniedException $e) {
            return $this->json(['message' => $e->getMessage()], 403);
        }
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, UserRepository $userRepository,UserPasswordHasherInterface $passwordHasher): Response
    {
        $entityManager = $this->entityManager;
        $user = new User($entityManager);
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $user_role = $this->entityManager->getRepository('App\Entity\Role')->find(2);
            $user->setRole($user_role);
            $plaintextPassword = $user->getPassword();
            //cast the user to the UserPasswordHasherInterface
            $hashedPassword = $passwordHasher->hashPassword($user, $plaintextPassword);
            $user->setPassword($hashedPassword);
            $userRepository->save($user, true);
            $session = $request->getSession();
            $session->set('user', $user);
            return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
        }else if($form->isSubmitted() && !$form->isValid()){
            $this->addFlash('error', 'The form is not valid');
        }
    
        return $this->renderForm('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }
    

    #[Route('/show/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user, Request $request): Response
    {
        try {
            $this->roleChecker->checkUserRole($request->getSession()->get('user'), 'Administrateur');
        } catch (AccessDeniedException $e) {
            return $this->json(['message' => $e->getMessage()], 403);
        }
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/edit/{id}', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, UserRepository $userRepository): Response
    {
        try {
            $this->roleChecker->checkUserRole($request->getSession()->get('user'), 'Administrateur');
        } catch (AccessDeniedException $e) {
            return $this->json(['message' => $e->getMessage()], 403);
        }
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userRepository->save($user, true);

            return $this->redirectToRoute('app_user_edit', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/edit_profile', name: 'app_user_edit_profile', methods: ['GET', 'POST'])]
    public function edit_profile(Request $request, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        try {
            $this->roleChecker->checkUserRole($request->getSession()->get('user'), 'Utilisateur');
        } catch (AccessDeniedException $e) {
            return $this->json(['message' => $e->getMessage()], 403);
        }
        
        $session = $request->getSession();
        $userId = $session->get('user')->getId();
        $user = $userRepository->find($userId);

        $roles = $entityManager->getRepository('App\Entity\Role')->findAll();   

        $form = $this->createForm(UsermodifyType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            dump($user);
            $userRepository->save($user, true);
            $session->set('user', $user);

            return $this->redirectToRoute('app_user_edit_profile', [], Response::HTTP_SEE_OTHER);
        } else if ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('error', 'The form is not valid');
            dump($form->getErrors(true));
        }

        return $this->renderForm('user/edit_profile.html.twig', [
            'user_id' => $user->getId(),
            'roles' => $roles,
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/delete/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, UserRepository $userRepository): Response
    {
        try {
            $this->roleChecker->checkUserRole($request->getSession()->get('user'), 'Administrateur');
        } catch (AccessDeniedException $e) {
            return $this->json(['message' => $e->getMessage()], 403);
        }
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $userRepository->remove($user, true);
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/login', name: 'app_user_login', methods: ['GET', 'POST'])]
    public function login(
        Request $request, 
        UserRepository $userRepository, 
        UserPasswordHasherInterface $passwordHasher
    ): Response
    {
        // Check if the user is logged in
        $session = $request->getSession();
        if ($session->get('user')) {
            // If yes, redirect to the index page
            return $this->redirectToRoute('app_home');
        }

        $error = null;
        // Check if form was submitted
        if ($request->getMethod() === 'POST') {
            // Get submitted email and password
            $email = $request->request->get('email');
            $plaintextPassword = $request->request->get('password');

            // Find user in database
            $user = $userRepository->findOneBy(['email' => $email]);

            // Check credentials
            if ($user && $passwordHasher->isPasswordValid($user, $plaintextPassword)) {
                // If credentials are correct, log user in and redirect to the index page
                $session->set('user', $user);
                return $this->redirectToRoute('app_home');
            } else {
                // If credentials are wrong, show the login form again with error
                $error = 'Invalid credentials';
            }
        }

        // If no, show the login form
        return $this->render('user/login.html.twig', [
            'error' => $error,
        ]);
    }

    #[Route('/logout', name: 'app_user_logout', methods: ['GET'])]
    public function logout(Request $request): Response
    {
        try {
            $this->roleChecker->checkUserRole($request->getSession()->get('user'), 'Utilisateur');
        } catch (AccessDeniedException $e) {
            return $this->json(['message' => $e->getMessage()], 403);
        }
        // Check if the user is logged in
        $session = $request->getSession();
        if ($session->get('user')) {
            // If yes, log user out
            $session->remove('user');
        }

        // Redirect to the index page
        return $this->redirectToRoute('app_home');
    }
    
    #[Route('/export_rgpd', name: 'app_user_export_rgpd', methods: ['GET'])]
    public function export_rgpd(Request $request, EntityManagerInterface $entityManager): Response
    {
        try {
            $this->roleChecker->checkUserRole($request->getSession()->get('user'), 'Utilisateur');
        } catch (AccessDeniedException $e) {
            return $this->json(['message' => $e->getMessage()], 403);
        }

        $userId = $request->getSession()->get('user')->getId();

        $queryBuilder = $entityManager->createQueryBuilder()
            ->select('u, cf, ct, m')
            ->from(User::class, 'u')
            ->leftJoin('u.conversationsFrom', 'cf')
            ->leftJoin('u.conversationsTo', 'ct')
            ->leftJoin('u.messages', 'm')
            ->where('u.id = :userId')
            ->setParameter('userId', $userId);

        $user = $queryBuilder->getQuery()->getOneOrNullResult();

        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        $user_data = [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'address_city' => $user->getAddressCity(),
            'address_zipcode' => $user->getAddressZipcode(),
            'address_country' => $user->getAddressCountry(),
            'role' => $user->getRole(),
            'conversations_from' => $user->getConversationsFrom()->toArray(),
            'conversations_to' => $user->getConversationsTo()->toArray(),
            'messages' => $user->getMessages()->toArray(),
        ];

        // Télécharger en tant que fichier JSON
        $filename = 'user_export_rgpd_'.date('Ymd').'_'.$user->getLastName().'.json';
        $response = new Response(json_encode($user_data));
        $response->headers->set('Content-Type', 'application/json');
        $response->headers->set('Content-Disposition', 'attachment;filename="'.$filename.'"');

        return $response;
    }

    #[Route('/get_conversation_from_user_for_plant/{user}/{plant}', name: 'app_user_get_conversation_from_user_for_plant', methods: ['GET'])]
    public function getConversationFromUserForPlant(Request $request, User $user, Plant $plant): Response
    {
        try {
            $this->roleChecker->checkUserRole($request->getSession()->get('user'), 'Utilisateur');
        } catch (AccessDeniedException $e) {
            return $this->json(['message' => $e->getMessage()], 403);
        }
        $currentUser = $request->getSession()->get('user');

        $conversation = $this->conversationRepository->findConversationByUsersAndPlant($currentUser, $user, $plant);

        if ($conversation) {
            return $this->json(['conversation_id' => $conversation->getId()]);
        } else {
            return $this->json(['conversation_id' => 0]);
        }
    }

}
<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Form\UsermodifyType;
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
    public function __construct(EntityManagerInterface $entityManager, RoleChecker $roleChecker)
    {
        $this->entityManager = $entityManager;
        $this->roleChecker = $roleChecker;
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
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $user_role = $this->entityManager->getRepository('App\Entity\Role')->find(2);
            dump($user_role); // Affichez le rôle pour vérifier qu'il a été récupéré correctement
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
    public function edit_profile(Request $request, UserRepository $userRepository,EntityManagerInterface $entityManager): Response
    {
        try {
            $this->roleChecker->checkUserRole($request->getSession()->get('user'), 'Utilisateur');
        } catch (AccessDeniedException $e) {
            return $this->json(['message' => $e->getMessage()], 403);
        }
        $session = $request->getSession();
        $user = $session->get('user');
        
        $user = $entityManager->merge($user);
        $roles = $entityManager->getRepository('App\Entity\Role')->findAll();

        $form = $this->createForm(UsermodifyType::class, $user);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $userRepository->save($user, true);
            $session->set('user', $user);
            return $this->redirectToRoute('app_user_edit_profile', [], Response::HTTP_SEE_OTHER);
        }else if($form->isSubmitted() && !$form->isValid()){
            $this->addFlash('error', 'The form is not valid');
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

}

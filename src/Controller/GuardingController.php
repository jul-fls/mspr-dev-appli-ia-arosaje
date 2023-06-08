<?php

namespace App\Controller;

use App\Entity\Guarding;
use App\Form\GuardingType;
use App\Repository\GuardingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/guarding')]
class GuardingController extends AbstractController
{
    #[Route('/', name: 'app_guarding_index', methods: ['GET'])]
    public function index(GuardingRepository $guardingRepository): Response
    {
        return $this->render('guarding/index.html.twig', [
            'guardings' => $guardingRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_guarding_new', methods: ['GET', 'POST'])]
    public function new(Request $request, GuardingRepository $guardingRepository): Response
    {
        $guarding = new Guarding();
        $form = $this->createForm(GuardingType::class, $guarding);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $guardingRepository->save($guarding, true);

            return $this->redirectToRoute('app_guarding_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('guarding/new.html.twig', [
            'guarding' => $guarding,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_guarding_show', methods: ['GET'])]
    public function show(Guarding $guarding): Response
    {
        return $this->render('guarding/show.html.twig', [
            'guarding' => $guarding,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_guarding_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Guarding $guarding, GuardingRepository $guardingRepository): Response
    {
        $form = $this->createForm(GuardingType::class, $guarding);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $guardingRepository->save($guarding, true);

            return $this->redirectToRoute('app_guarding_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('guarding/edit.html.twig', [
            'guarding' => $guarding,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_guarding_delete', methods: ['POST'])]
    public function delete(Request $request, Guarding $guarding, GuardingRepository $guardingRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$guarding->getId(), $request->request->get('_token'))) {
            $guardingRepository->remove($guarding, true);
        }

        return $this->redirectToRoute('app_guarding_index', [], Response::HTTP_SEE_OTHER);
    }
}

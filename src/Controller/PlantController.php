<?php

namespace App\Controller;

use App\Entity\Plant;
use App\Form\PlantType;
use App\Repository\PlantRepository;
use App\Service\RoleChecker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/plant')]
class PlantController extends AbstractController
{
    private $roleChecker;

    private $entityManager;
    public function __construct(EntityManagerInterface $entityManager, RoleChecker $roleChecker)
    {
        $this->entityManager = $entityManager;
        $this->roleChecker = $roleChecker;
    }

    #[Route('/', name: 'app_plant_index', methods: ['GET'])]
    public function index(PlantRepository $plantRepository, Request $request): Response
    {
        try {
            $this->roleChecker->checkUserRole($request->getSession()->get('user'), 'Administrateur');
        } catch (AccessDeniedException $e) {
            return $this->json(['message' => $e->getMessage()], 403);
        }
        return $this->render('plant/index.html.twig', [
            'plants' => $plantRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_plant_new', methods: ['GET', 'POST'])]
    public function new(Request $request, PlantRepository $plantRepository): Response
    {
        try {
            $this->roleChecker->checkUserRole($request->getSession()->get('user'), 'Utilisateur');
        } catch (AccessDeniedException $e) {
            return $this->json(['message' => $e->getMessage()], 403);
        }
        $plant = new Plant();
        $form = $this->createForm(PlantType::class, $plant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plantRepository->save($plant, true);

            return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('plant/new.html.twig', [
            'plant' => $plant,
            'form' => $form,
        ]);
    }

    #[Route('/show/show/{id}', name: 'app_plant_show', methods: ['GET'])]
    public function show(Plant $plant, Request $request): Response
    {
        try {
            $this->roleChecker->checkUserRole($request->getSession()->get('user'), 'Botaniste');
        } catch (AccessDeniedException $e) {
            return $this->json(['message' => $e->getMessage()], 403);
        }
        return $this->render('plant/show.html.twig', [
            'plant' => $plant,
        ]);
    }

    #[Route('/get/{id}', name: 'app_plant_get_json', methods: ['GET'])]
    public function get_json(Plant $plant, Request $request): Response
    {
        try {
            $this->roleChecker->checkUserRole($request->getSession()->get('user'), 'Botaniste');
        } catch (AccessDeniedException $e) {
            return $this->json(['message' => $e->getMessage()], 403);
        }
        return new JsonResponse($plant);
    }

    #[Route('/edit/{id}', name: 'app_plant_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Plant $plant, PlantRepository $plantRepository): Response
    {
        try {
            $this->roleChecker->checkUserRole($request->getSession()->get('user'), 'Utilisateur');
            if($plant->getOwner() !== $request->getSession()->get('user')) {
                throw new AccessDeniedException('Vous n\'avez pas les droits suffisants pour accéder à cette ressource.');
            }
        } catch (AccessDeniedException $e) {
            return $this->json(['message' => $e->getMessage()], 403);
        }
        $form = $this->createForm(PlantType::class, $plant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plantRepository->save($plant, true);

            return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('plant/edit.html.twig', [
            'plant' => $plant,
            'form' => $form,
        ]);
    }

    #[Route('/publish/{id}/{is_published}', name: 'app_plant_pub_unpub', methods: ['GET'])]
    public function pub_unpub(Request $request, Plant $plant, PlantRepository $plantRepository): Response
    {
        try {
            $this->roleChecker->checkUserRole($request->getSession()->get('user'), 'Utilisateur');
            if($plant->getOwner() !== $request->getSession()->get('user')) {
                throw new AccessDeniedException('Vous n\'avez pas les droits suffisants pour accéder à cette ressource.');
            }
        } catch (AccessDeniedException $e) {
            return $this->json(['message' => $e->getMessage()], 403);
        }
        // rest api route to publish / unpublish the plant specified by id and the boolean is_published in the route parameters
        $is_published = $request->attributes->get('is_published') === 'true'; // Cast to boolean
        $plant->setIsPublished($is_published);
        $plantRepository->save($plant, true);
        // return 200 OK
        return new JsonResponse(['status' => 'ok']);
    }

    #[Route('/delete/{id}', name: 'app_plant_delete', methods: ['POST'])]
    public function delete(Request $request, Plant $plant, PlantRepository $plantRepository): Response
    {
        try {
            $this->roleChecker->checkUserRole($request->getSession()->get('user'), 'Utilisateur');
            if($plant->getOwner() !== $request->getSession()->get('user')) {
                throw new AccessDeniedException('Vous n\'avez pas les droits suffisants pour accéder à cette ressource.');
            }
        } catch (AccessDeniedException $e) {
            return $this->json(['message' => $e->getMessage()], 403);
        }
        $plantRepository->remove($plant, true);
        return $this->redirectToRoute('app_plant_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/identify', name: 'app_plant_identify', methods: ['POST'])]
    public function identify(Request $request)
    {
        try {
            $this->roleChecker->checkUserRole($request->getSession()->get('user'), 'Utilisateur');
        } catch (AccessDeniedException $e) {
            return $this->json(['message' => $e->getMessage()], 403);
        }
        $url = $this->getParameter('plantnet_api_url').'/identify/all?lang=fr&api-key='.$this->getParameter('plantnet_api_key');

        $image = $request->files->get('image');

        $data = [
            'images' => curl_file_create($image->getRealPath()),
            'organs' => 'auto'
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if(curl_errno($ch)){
            throw new \Exception(curl_error($ch));
        }

        curl_close($ch);

        if ($httpcode == 404) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_NO_CONTENT);
            return $response;
        }
        
        return new JsonResponse(json_decode($response, true));
    }
}

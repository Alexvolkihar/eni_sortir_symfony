<?php

namespace App\Controller;

use App\Entity\Place;
use App\Form\PlaceType;
use App\Repository\PlaceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PlaceController extends AbstractController
{
    #[Route('/place', name: 'app_place')]
    public function index(PlaceRepository $placeRepository,Request $request,EntityManagerInterface $entityManager,): Response
    {
        $place = new Place();
        $form = $this->createForm( PlaceType::class ,$place);
        $form->handleRequest($request);
        $places  = $placeRepository->findAll();
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($place);
            $entityManager->flush();
            $places  = $placeRepository->findAll();
        }
        return $this->render('place/place.html.twig', [
            "place" => $places,
            "form" => $form->createView()
        ]);
    }
    #[Route('/delete/{id}', name: 'app_delete', requirements: ['id' => '[0-9]+'])]
    public function delete(
        int $id,
        EntityManagerInterface $entityManager,
        PlaceRepository        $placeRepository): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        $place = $placeRepository->find($id);
        $entityManager->remove($place);

        $entityManager->flush();
        $this->addFlash('success', 'truc ' . $place->getName() . ' Supprimer !');
        return $this->redirectToRoute("app_place");
    }
}

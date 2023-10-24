<?php

namespace App\Controller;

use App\Entity\City;
use App\Form\CityType;
use App\Repository\CityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CityController extends AbstractController
{
    #[Route('/city', name: 'app_city')]
    public function index(CityRepository $cityRepository, EntityManagerInterface $entityManager, Request $request): Response
    {
        $city = new City();
        $form = $this->createForm(CityType::class, $city);
        $form->handleRequest($request);
        $cities  = $cityRepository->findAll();
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($city);
            $entityManager->flush();
            $cities  = $cityRepository->findAll();
        }
        return $this->render('city/city.html.twig', [
            "city" => $cities,
            "form" => $form->createView()
        ]);
    }

    #[Route('/city/delete/{id}', name: 'app_delete_city', requirements: ['id' => '[0-9]+'])]
    public function delete(
        int $id,
        EntityManagerInterface $entityManager,
        CityRepository        $cityRepository
    ): \Symfony\Component\HttpFoundation\RedirectResponse {
        $city = $cityRepository->find($id);

        $entityManager->remove($city);
        $entityManager->flush();
        $this->addFlash('success', 'Ville ' . $city->getName() . ' Supprimer !');
        return $this->redirectToRoute("app_city");
    }
}

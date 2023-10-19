<?php

namespace App\Controller;

use App\Form\EventType;
use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class HomeController extends AbstractController
{
    #[Route(path: '/home', name: 'app_home')]
    public function index(EntityManagerInterface $entityManager, Request $request, EventRepository $eventRepository): Response
    {
        $events = [];
        $form = $this->createForm(EventType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérez les données du formulaire
            $formData = $form->getData();

            // Effectuez la recherche d'événements en utilisant les données du formulaire
            $events = $eventRepository->findBy([
                'name' => $formData['name'],
                // Ajoutez d'autres critères de recherche si nécessaire
            ]);
        }

        return $this->render('home/city.html.twig', [
            'controller_name' => 'HomeController',
            'form' => $form->createView(),
            'events' => $events,
        ]);
    }
}

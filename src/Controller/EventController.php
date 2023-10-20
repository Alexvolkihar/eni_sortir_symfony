<?php

namespace App\Controller;

use App\Entity\City;
use App\Entity\Event;
use App\Entity\Place;
use App\Entity\State;
use App\Form\EventOutCityType;
use App\Form\EventOutType;
use App\Data\SearchEvent;
use App\Form\EventSearchType;
use App\Repository\EventRepository;
use App\Repository\StateRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class EventController extends AbstractController
{
    public function __construct(
        private Security $security,
    ) {
    }


    #[Route(path: '/', name: 'events_index')]
    public function index(EntityManagerInterface $entityManager, Request $request, EventRepository $eventRepository, UserRepository $userRepository): Response
    {
        $searchEvent = new SearchEvent();
        $user = $userRepository->findOneBy(['email' => $this->getUser()->getUserIdentifier()]);
        $searchEvent->site = $user->getSite();
        $searchEvent->user = $user;

        $eventsSearchForm = $this->createForm(EventSearchType::class, $searchEvent);
        $events = $eventRepository->searchFind($searchEvent);

        $eventsSearchForm->handleRequest($request);
        if ($eventsSearchForm->isSubmitted() && $eventsSearchForm->isValid()) {
            $events = $eventRepository->searchFind($searchEvent);
        }

        return $this->render('event/list.html.twig', [
            'eventsSearchForm' => $eventsSearchForm->createView(),
            'events' => $events,
        ]);
    }
    #[Route(path: '/createEvent', name: 'events_create')]
    public function createEvent(Request $request, EntityManagerInterface $entityManager): Response
    {
        $event = new Event();

        $eventCreateForm = $this->createForm(EventOutType::class,$event);
        $eventCreateFormCity = $this->createForm(EventOutCityType::class);


        $state = new State();
        $state->setLabel('Créée');
        $event->setState($state);

        $eventCreateForm->handleRequest($request);
        $eventCreateFormCity->handleRequest($request);

        if($eventCreateForm->isSubmitted()&& $eventCreateForm->isValid()){

            $entityManager->persist($event);
            $entityManager->flush();
        }

        return $this->render('event/createEvent.html.twig', [
           'eventCreateForm' => $eventCreateForm->createView(),
            'eventCreateFormCity' => $eventCreateFormCity->createView()
        ]);
    }

    #[Route(path: '/getPostalCode/{cityId}', name: 'get_postal_code', methods: ['GET'])]
    public function getPostalCode($cityId, EntityManagerInterface $entityManager): JsonResponse
    {
        $repository = $entityManager->getRepository(City::class);
        $repositoryStreet = $entityManager->getRepository(Place::class);
        $city = $repository->find($cityId);
        $street = $repositoryStreet->findOneBy(['city'=>$cityId]);

        if(!$city | !$street){
            return new JsonResponse(['postalCode' => '','street' => '']);
        }
        $postalCode = $city->getPostalCode();
        $streets = $street->getName();

        return new JsonResponse(['postalCode' => $postalCode,'street' => $streets]);
    }

}

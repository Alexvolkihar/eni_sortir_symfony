<?php

namespace App\Controller;

use App\Entity\City;
use App\Entity\Event;
use App\Entity\Place;
use App\Form\EventOutType;
use App\Data\SearchEvent;
use App\Form\EventSearchType;
use App\Repository\EventRepository;
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

    // ajoute une vérification si pas connecté redirige vers la page de connexion, si connecté redirige vers la page d'accuei
    #[Route(path: '/', name: 'events_index')]
    public function index(EntityManagerInterface $entityManager, Request $request, EventRepository $eventRepository, UserRepository $userRepository): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

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

    #[Route(path: '/event/{id}', name: 'event_show')]
    public function show(Event $event): Response
    {
        return $this->render('event/show.html.twig', [
            'event' => $event,
        ]);
    }

    #[Route(path: 'event/sub/{id}', name: 'event_sub')]
    public function eventSub(Event $event, EntityManagerInterface $entityManager): Response
    {

        $user = $this->security->getUser();
        $event->addMember($user);
        $entityManager->persist($event);
        $entityManager->flush();

        return $this->redirectToRoute('events_index');
    }

    #[Route(path: 'event/unsub/{id}', name: 'event_unsub')]
    public function eventUnsub(Event $event, EntityManagerInterface $entityManager): Response
    {
        $user = $this->security->getUser();
        $event->removeMember($user);
        $entityManager->persist($event);
        $entityManager->flush();

        return $this->redirectToRoute('events_index');
    }

    #[Route(path: '/createEvent', name: 'events_create')]
    public function createEvent(Request $request, EntityManagerInterface $entityManager): Response
    {

        $event = new Event();

        $eventCreateForm = $this->createForm(EventOutType::class, $event);
        $test = $this->getUser();
        $event->setHost($test);
        $eventCreateForm->handleRequest($request);
        // $eventCreateFormCity->handleRequest($request);

        if ($eventCreateForm->isSubmitted() && $eventCreateForm->isValid()) {

            $entityManager->persist($event);
            $entityManager->flush();
            return $this->redirectToRoute('events_index');
        }

        return $this->render('event/new', [
            'eventCreateForm' => $eventCreateForm->createView(),
            //'eventCreateFormCity' => $eventCreateFormCity->createView()
        ]);
    }

    #[Route(path: '/getPostalCode/{cityId}', name: 'get_postal_code', methods: ['GET'])]
    public function getPostalCode($cityId, EntityManagerInterface $entityManager): JsonResponse
    {
        $repository = $entityManager->getRepository(City::class);
        $repositoryStreet = $entityManager->getRepository(Place::class);
        $city = $repository->find($cityId);
        $street = $repositoryStreet->findOneBy(['city' => $cityId]);

        if (!$city | !$street) {
            return new JsonResponse(['postalCode' => '', 'street' => '']);
        }
        $postalCode = $city->getPostalCode();
        $streets = $street->getName();

        return new JsonResponse(['postalCode' => $postalCode, 'street' => $streets]);
    }

    #[Route(path: 'event/annuler/{id}', name: 'event_annuler')]
    public function eventAnnuler($id, Event $event, EntityManagerInterface $entityManager): Response
    {
        $newState = $entityManager->getRepository(State::class)->find(12);

        if (!$newState) {
            throw $this->createNotFoundException('État non trouvé avec l\'ID 12');
        }
        if (!$entityManager->getRepository(State::class)->find(10)) {
            $event->setState($newState);

            $entityManager->persist($event);
            $entityManager->flush();


            return $this->redirectToRoute('events_index');
        } else {
            $this->addFlash("error", 'Impossible de changer le status si levent a commencé mec');
        }
        return $this->redirectToRoute('event_show', ['id' => $id]);
    }
}

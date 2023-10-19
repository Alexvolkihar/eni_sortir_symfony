<?php

namespace App\Controller;

use App\Entity\Event;
use App\Data\SearchEvent;
use App\Form\EventSearchType;
use App\Repository\EventRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
}

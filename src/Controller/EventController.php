<?php

namespace App\Controller;

use App\Entity\Event;
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

    // ajoute une vérification si pas connecté redirige vers la page de connexion, si connecté redirige vers la page d'accuei
    #[Route(path: '/', name: 'events_index')]
    public function index(EntityManagerInterface $entityManager, Request $request, EventRepository $eventRepository, UserRepository $userRepository): Response
    {
        if ($this->security->getUser()) {
            $eventSearch = new Event();
            $user = $userRepository->findOneBy(['email' => $this->getUser()->getUserIdentifier()]);

            $eventSearch->setSite($user->getSite());
            $eventsSearchForm = $this->createForm(EventSearchType::class, $eventSearch);
            $events = $eventRepository->findAll();

            $eventsSearchForm->handleRequest($request);

            if ($eventsSearchForm->isSubmitted() && $eventsSearchForm->isValid()) {
                $formData = $eventsSearchForm->getData();

                dd($formData);
            }

            return $this->render('event/list.html.twig', [
                'eventsSearchForm' => $eventsSearchForm->createView(),
                'events' => $events,
            ]);
        }
        return $this->redirectToRoute('app_login');
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
}

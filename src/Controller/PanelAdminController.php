<?php

namespace App\Controller;

use App\Entity\User;

use App\Form\UserType;
use App\Repository\EventRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PanelAdminController extends AbstractController
{
    #[Route('/panel/admin', name: 'app_panel_admin')]
    public function index(Request $request, UserRepository $userRepository): Response
    {
       $admin = $this->getUser();
       $recupeRole = $admin->getRoles();
       $implode = implode(',',$recupeRole);
       $user = new User();
       $form = $this->createForm( UserType::class,$user);
       $form->handleRequest($request);

       $users  = $userRepository->findAll();
       if (str_contains($implode, "ROLE_ADMIN")) {
           return $this->render('panel_admin/index.html.twig', [
               "users" => $users,
               "form" => $form->createView()
           ]);
       }

        return $this->redirectToRoute('events_index');
    }

    #[Route('/delete/{id}', name: 'app_delete', requirements: ['id' => '[0-9]+'])]
    public function delete(
        int $id,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        EventRepository $eventRepository
    ): \Symfony\Component\HttpFoundation\RedirectResponse {
        $user = $userRepository->find($id);

        // Supprimer tous les événements où l'utilisateur est l'hôte
        foreach ($user->getEvents() as $event) {
            // Supprimer les références à l'utilisateur dans les événements
            $event->setHost(null);
            // Supprimer tous les membres de l'événement
            foreach ($event->getMembers() as $member) {
                $event->removeMember($member);
            }
            // Supprimer l'événement
            $entityManager->remove($event);
        }

        // Supprimer tous les événements où l'utilisateur est membre
        foreach ($user->getSubEvents() as $subEvent) {
            // Supprimer les références à l'utilisateur dans les événements
            $subEvent->removeMember($user);
            // Supprimer l'utilisateur de l'événement
            $user->removeSubEvent($subEvent);
        }

        // Supprimer l'utilisateur
        $entityManager->remove($user);
        $entityManager->flush();

        $this->addFlash('success', 'Utilisateur ' . $user->getName() . ' Supprimé !');
        return $this->redirectToRoute("app_panel_admin");
    }

    #[Route('/active/{id}', name: 'app_active', requirements: ['id' => '[0-9]+'])]
    public function active(int $id,
                           EntityManagerInterface $entityManager,
                           UserRepository $userRepository,
                           EventRepository $eventRepository): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        $user = $userRepository->find($id);
        if($user->isActive()){
            $user->setIsActive(false);
        }else{
            $user->setIsActive(true);
        }
        $entityManager->flush();

        return $this->redirectToRoute("app_panel_admin");
    }
}

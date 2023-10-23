<?php

namespace App\Controller;

use App\Form\RegistrationFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use App\Security\AppAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use App\Utils\Uploader;
use App\Entity\User;




#[Route('/profile', name: 'profile_')]
class ProfileController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('profile/index.html.twig', [
            'controller_name' => 'ProfileController',
        ]);
    }
    #[Route('/edit', name: 'edit')]
    public function edit(Request $request, UserAuthenticatorInterface $userAuthenticator, AppAuthenticator $authenticator, EntityManagerInterface $entityManager, Uploader $uploader): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(RegistrationFormType::class, $user, [
            'mapped' => false, // Désactive le mappage par défaut des champs du formulaire à l'objet User
        ]);

        // Remplacez les champs que vous souhaitez utiliser pour la mise à jour
        $form
            ->remove('agreeTerms')     // Exclut le champ agreeTerms
            ->remove('isAdmin')        // Exclut le champ isAdmin
            ->remove('isActive');    // Exclut le champ isActive

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // $user->setAvatar(
            //     $uploader->upload(
            //         $form->get('avatar')->getData(),
            //         $this->getParameter('upload_avatar_dir'),
            //         $user->getName()
            //     )
            // );

            $entityManager->persist($user);
            $entityManager->flush();

            return $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
            );
        }

        return $this->render('profile/edit.html.twig', [
            'controller_name' => 'ProfileController',
            'registrationForm' => $form->createView(),
        ]);
    }
    #[Route(('/{id}'), name: 'show')]
    public function show(User $user): Response
    {
        return $this->render('profile/show.html.twig', [
            'user' => $user,
        ]);
    }
}
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
    public function edit(Request $request, UserAuthenticatorInterface $userAuthenticator, AppAuthenticator $authenticator, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(RegistrationFormType::class, $user, [
            'mapped' => false, // Désactive le mappage par défaut des champs du formulaire à l'objet User
        ]);

        // Remplacez les champs que vous souhaitez utiliser pour la mise à jour
        $form
            ->remove('agreeTerms')     // Exclut le champ agreeTerms
            ->remove('isAdmin')        // Exclut le champ isAdmin
            ->remove('isActive')   ;    // Exclut le champ isActive

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Effectuez vos opérations de mise à jour ici

            // Par exemple, si vous avez ajouté un nouveau champ
            // $newFieldValue = $form->get('newField')->getData();
            // $user->setNewField($newFieldValue);

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
    
}

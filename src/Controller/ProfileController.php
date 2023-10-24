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
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;




#[Route('/profile', name: 'profile_')]
class ProfileController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(UserRepository $userRepository): Response
    {
        $user = $userRepository->findOneBy(['email' => $this->getUser()->getUserIdentifier()]);
        return $this->render('profile/show.html.twig', [
            'user' => $user,
        ]);
    }
    #[Route('/edit', name: 'edit')]
    public function edit(Request $request, UserAuthenticatorInterface $userAuthenticator, UserRepository $userRepository, AppAuthenticator $authenticator, EntityManagerInterface $entityManager, Uploader $uploader): Response
    {
        $user = $userRepository->findOneBy(['email' => $this->getUser()->getUserIdentifier()]);
        $form = $this->createForm(RegistrationFormType::class, $user, [
            'mapped' => false,
        ]);
        $form
            ->remove('agreeTerms')     // Exclude the agreeTerms field
            ->remove('isAdmin')        // Exclude the isAdmin field
            ->remove('isActive');     // Exclude the isActive field
        $form->handleRequest($request);
        /**
         * @var UploadedFile $avatar
         */

        $avatar = $form->get('avatar')->getData();
        if ($form->isSubmitted() && $form->isValid()) {

            if ($avatar) {
                $user->setAvatar(
                    $uploader->upload(
                        $avatar,
                        $this->getParameter('upload_avatar_dir'),
                        $user->getName()
                    )
                );
            }
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

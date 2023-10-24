<?php

namespace App\Controller;

use App\Entity\Site;
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\AppAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, UserAuthenticatorInterface $userAuthenticator, AppAuthenticator $authenticator, EntityManagerInterface $entityManager): Response
    {

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);
        if ($request->files->get('csv_file')) {
            /** @var UploadedFile $csvFile */
            $csvFile = $request->files->get('csv_file');
            $csvData = file_get_contents($csvFile->getPathname());

            // Parse CSV data and create users
            $rows = explode(PHP_EOL, $csvData);
            foreach ($rows as $row) {
                $userData = str_getcsv($row);
                $user = new User();
                $user->setEmail($userData[0]);
                $user->setPassword($userData[1]);
                $user->setName($userData[2]);
                $user->setLastname($userData[3]); // Assuming lastname is the fourth column in CSV
                $user->setPhone($userData[4]); // Assuming phone is the fifth column in CSV
                $user->setPseudo($userData[5]); // Assuming pseudo is the sixth column in CSV

                // Assuming site is the seventh column in CSV, you need to fetch the Site entity based on the value in $userData[6]
                $site = $entityManager->getRepository(Site::class)->findOneBy(['name' => $userData[6]]);
                $user->setSite($site);

                // Assuming isAdmin is the ninth column in CSV
                $user->setIsAdmin(boolval($userData[7]));

                // Assuming isActive is the tenth column in CSV
                $user->setIsActive(boolval($userData[8]));

                $user->setRoles(['ROLE_USER']);

                // Encode password and save user
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $user->getPassword()
                    )
                );

                $entityManager->persist($user);
            }

            $entityManager->flush();

            // Redirect, display a message, or do anything else you need after processing the CSV file
            return $this->redirectToRoute('events_index');
        }
        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            $user->setRoles(['ROLE_USER']);


            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email

            return $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
            );
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}

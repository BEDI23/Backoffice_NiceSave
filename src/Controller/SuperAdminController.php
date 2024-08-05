<?php

namespace App\Controller;

use App\Entity\SuperAdmin;
use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class SuperAdminController extends AbstractController
{
    #[Route('/edit/SuperAdmin', name: 'app_editSuperAdmin')]
    public function edit(Request $request, EntityManagerInterface $em, Security $security): Response
    {
        $user = $security->getUser();

        if (!$user instanceof SuperAdmin) {
            throw $this->createAccessDeniedException('You do not have access to this section.');
        }

        if ($request->isMethod('POST')) {
            $firstname = $request->request->get('firstname');
            $lastname = $request->request->get('lastname');
            $email = $request->request->get('email');

            $user->setFirstname($firstname);
            $user->setLastname($lastname);
            $user->setEmail($email);

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Profil mis à jour avec succès !');

            return $this->redirectToRoute('app_editSuperAdmin');
        }

        return $this->render('super_admin/ProfileSuperAdmin.html.twig', [
            'user' => $user,
        ]);
    }
    #[Route('/edit/ChangePassword', name: 'app_ChangePasswordSuper')]
    public function changePassword(Request $request, UserPasswordHasherInterface $passwordHasher, Security $security,
                                   EntityManagerInterface $em): Response
    {
        $user = $security->getUser();

        if (!$user instanceof SuperAdmin) {
            throw $this->createAccessDeniedException('You do not have access to this section.');
        }

        if ($request->isMethod('POST')) {
            $currentPassword = $request->request->get('password');
            $newPassword = $request->request->get('newpassword');
            $renewPassword = $request->request->get('renewpassword');

            // Vérifiez si le nouveau mot de passe et la confirmation correspondent
            if ($newPassword !== $renewPassword) {
                $this->addFlash('error', 'Les nouveaux mots de passe ne correspondent pas.');
                return $this->redirectToRoute('app_editSuperAdmin'); // Remplacez cette route par la route appropriée
            }

            // Vérifiez si l'ancien mot de passe est correct
            if (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
                $this->addFlash('error', 'L\'ancien mot de passe est incorrect.');
                return $this->redirectToRoute('app_editSuperAdmin'); // Remplacez cette route par la route appropriée
            }

            // Encodez et mettez à jour le mot de passe
            $encodedPassword = $passwordHasher->hashPassword($user, $newPassword);
            $user->setPassword($encodedPassword);

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Mot de passe changé avec succès.');

            return $this->redirectToRoute('app_editSuperAdmin'); // Remplacez cette route par la route appropriée
        }

        return $this->render('super_admin/ProfileSuperAdmin.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/Admin', name: 'app_admin')]
    public function showAdmin(): Response
    {
        return $this->render('super_admin/ProfileAdmin.html.twig', [
            'controller_name' => 'SuperAdminController',]);
    }

    #[Route('/register/Admin', name: 'app_registerAdmin')]
    public function register(Request $request, UserPasswordHasherInterface
                                     $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();

            // do anything else you need here, like send an email

            return $this->redirectToRoute('app_admin');
        }

        return $this->render('super_admin/NewAdmin.html.twig', [
            'registrationForm' => $form,
        ]);
    }

}

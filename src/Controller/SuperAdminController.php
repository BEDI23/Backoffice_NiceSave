<?php

namespace App\Controller;

use App\Entity\Admin;
use App\Entity\SuperAdmin;
use App\Repository\AdminRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class SuperAdminController extends AbstractController
{

    #[Route('/edit/profile', name: 'app_editSuperAdmin')]
    public function edit(Request $request, EntityManagerInterface $em, Security $security): Response
    {
        $user = $security->getUser();

        if (!$user instanceof SuperAdmin & !$user instanceof Admin  ) {
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

        return $this->render('super_admin/Profile.html.twig', [
            'user' => $user,
        ]);
    }
    #[Route('/ChangePassword/profile', name: 'app_ChangePasswordSuper')]
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

        return $this->render('super_admin/Profile.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/super-admin/Admin', name: 'app_admin')]
    public function showAdmin(AdminRepository $adminRepository): Response
    {
        $admins = $adminRepository->findAll();

        return $this->render('super_admin/ListeProfileAdmin.html.twig', [
            'admins' => $admins,
        ]);
    }

    #[Route('/super-admin/admin/{id}/delete', name: 'app_DeleteAdmin', methods: ['POST'])]
    public function DeleteAdmin(AdminRepository $adminRepository, int $id, EntityManagerInterface $entityManager): Response
    {
        $admin = $adminRepository->find($id);

        if (!$admin) {
            throw $this->createNotFoundException('Administrateur non trouvé');
        }

        $entityManager->remove($admin);
        $entityManager->flush();

        $this->addFlash('success', 'Administrateur supprimé avec succès.');

        return $this->redirectToRoute('app_admin');
    }




    #[Route('/super-admin/register/Admin', name: 'app_registerAdmin')]
    public function register(Request $request, EntityManagerInterface $em,
                             UserPasswordHasherInterface $passwordHasher): Response
    {
        if ($request->isMethod('POST')) {
            // Récupération des données du formulaire
            $firstname = $request->request->get('firstname');
            $lastname = $request->request->get('lastname');
            $email = $request->request->get('email');
            $plainPassword = $request->request->get('plainPassword');

            // Validation des données
            if (empty($firstname) || empty($lastname) || empty($email) || empty($plainPassword)) {
                $this->addFlash('error', 'Tous les champs doivent être remplis.');
                return $this->redirectToRoute('app_registerAdmin');
            }

            // Création d'un nouvel utilisateur
            $admin = new Admin();
            $admin->setFirstname($firstname);
            $admin->setLastname($lastname);
            $admin->setEmail($email);
            $admin->setRoles(['ADMINISTRATEUR']);
            $admin->setPassword($passwordHasher->hashPassword($admin, $plainPassword));
            $admin->setDateCreate();


            // Enregistrement de l'utilisateur dans la base de données
            $em->persist($admin);
            $em->flush();

            $this->addFlash('success', 'L\'administrateur a été ajouté avec succès.');

            return $this->redirectToRoute('app_admin');
        }

        return $this->render('super_admin/NewAdmin.html.twig');
    }

    #[Route('/super-admin/admin/{id}/edit', name: 'app_editAdmin', methods: ['POST'])]
    public function editProfile(Request $request, AdminRepository $adminRepository,
                                int $id, EntityManagerInterface $em): Response
    {
        $admin = $adminRepository->find($id);

        if (!$admin) {
            throw $this->createNotFoundException('L\'administrateur n\'existe pas.');
        }

        // Récupérer les données du formulaire
        $firstname = $request->request->get('firstname');
        $lastname = $request->request->get('lastname');
        $email = $request->request->get('email');
        $address = $request->request->get('address');
        $Number = $request->request->get('phone');

        // Mettre à jour le profil de l'administrateur
        $admin->setFirstname($firstname);
        $admin->setLastname($lastname);
        $admin->setEmail($email);
        $admin->setAddress($address);
        $admin->setNumber($Number );

        // Sauvegarder les modifications
        $em->persist($admin);
        $em->flush();
        $this->addFlash('success', 'Profil mis à jour avec succès.');

        return $this->redirectToRoute('app_admin');
    }


    #[Route('/super-admin/admin/{id}/change-password', name: 'app_ChangePasswordAdmin', methods: ['POST'])]
    public function changePasswordAdmin(Request $request, AdminRepository $adminRepository,
                                        int $id, UserPasswordHasherInterface $passwordHasher,
                                        EntityManagerInterface $em): Response
    {
        $admin = $adminRepository->find($id);

        if (!$admin) {
            throw $this->createNotFoundException('L\'administrateur n\'existe pas.');
        }

        // Retrieve data from the request
        $newPassword = $request->request->get('newPassword');
        $renewPassword = $request->request->get('renewPassword');

        // Check password match
        if ($newPassword !== $renewPassword) {
            $this->addFlash('error', 'Les mots de passe ne correspondent pas.');
            return $this->redirectToRoute('app_admin');
        }

        // Encode and set new password
            $admin->setPassword($passwordHasher->hashPassword($admin, $newPassword));
            $em->persist($admin);
            $em->flush();
            $this->addFlash('success', 'Mot de passe changé avec succès.');


        return $this->redirectToRoute('app_admin');
    }
}

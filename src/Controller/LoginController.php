<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use Random\RandomException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
    #[Route(path: '/', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * @throws RandomException
     * @throws TransportExceptionInterface
     */
    #[Route('/forgot-password', name: 'app_forgot_password')]
    public function forgotPassword(Request $request, UserRepository $userRepository,
                                   EntityManagerInterface $em,MailerInterface $mailer): Response
    {
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');

            // Rechercher l'utilisateur par email
            $user = $userRepository->findOneBy(['email' => $email]);

            if ($user) {
                // Générer un token de réinitialisation
                $resetToken = bin2hex(random_bytes(32));
                $user->setResetToken($resetToken);

                // Enregistrer le token en base de données
                $em->flush();

                // Envoyer un email de réinitialisation
                $resetUrl = $this->generateUrl('app_reset_password', ['token' => $resetToken], UrlGeneratorInterface::ABSOLUTE_URL);

                $email = (new Email())
                    ->from('bedinadejosue39@gmail.com')
                    ->to($user->getEmail())
                    ->subject('Réinitialisation de votre mot de passe')
                    ->html('<p>
                                    Cliquez sur ce lien pour réinitialiser votre mot de passe : 
                                      <a href="' . $resetUrl . '"> Réinitialiser le mot de passe</a>
                                  </p>');

                $mailer->send($email);

                $this->addFlash('success', 'Un lien de réinitialisation de mot de passe a été envoyé à votre adresse email.');
            } else {
                $this->addFlash('danger', 'Aucun utilisateur trouvé avec cette adresse email.');
            }
        }

        return $this->render('security/forgot-password.html.twig');
    }

    #[Route('/reinitialiser-mot-de-passe/{token}', name: 'app_reset_password')]
    public function resetPassword(string $token, Request $request, UserRepository $userRepository,
                                  EntityManagerInterface $em,
                                  UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = $userRepository->findOneBy(['resetToken' => $token]);

        if (!$user) {
            $this->addFlash('danger', 'Token invalide.');
            return $this->redirectToRoute('app_forgot_password');
        }

        if ($request->isMethod('POST')) {
            $newPassword = $request->request->get('newPassword');
            $confirmPassword = $request->request->get('confirmPassword');

            if ($newPassword === $confirmPassword) {
                // Hash du nouveau mot de passe
                $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
                $user->setPassword($user);
                $user->setResetToken(null); // Réinitialiser le token

                // Enregistrer les modifications en base de données
                $em->flush();

                $this->addFlash('success', 'Votre mot de passe a été réinitialisé avec succès.');

                return $this->redirectToRoute('app_login');
            } else {
                $this->addFlash('danger', 'Les mots de passe ne correspondent pas.');
            }
        }

        return $this->render('security/reset-password.html.twig', [
            'token' => $token,
        ]);
    }


}

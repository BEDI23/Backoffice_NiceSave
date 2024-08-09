<?php

namespace App\Controller\Api;

use App\Entity\Customer;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class ApiLoginController extends AbstractController
{
    #[Route('/api/login_check', name: 'app_api_login')]
    public function login_check(Request $request, UserRepository $userRepository,
                          UserPasswordHasherInterface $passwordHasher,
                          JWTTokenManagerInterface $jwtManager): JsonResponse
    {
        // Extraire les données JSON de la requête
        $data = json_decode($request->getContent(), true);

        // Vérifier que les données ont bien été extraites
        if (is_null($data) || !isset($data['email']) || !isset($data['password'])) {
            return new JsonResponse(['message' => 'Données JSON invalides'], 400);
        }

        // Rechercher l'utilisateur par email
        $user = $userRepository->findOneBy(['email' => $data['email']]);

        // Vérifier que l'utilisateur existe et que le mot de passe est correct
        if (!$user || !$passwordHasher->isPasswordValid($user, $data['password'])) {
            return new JsonResponse(['message' => 'Identifiants invalides'], 401);
        }

        // Générer le token JWT
        $token = $jwtManager->create($user);

        // Retourner le token dans la réponse JSON
        return new JsonResponse(['token' => $token], 200);
    }

    #[Route('/api/test', name: 'app_api_test',)]
    public function test(): Response
    {
        return new Response('Vos est connecter ');
    }

    #[Route('/register/api', name: 'api_register', methods: ['POST','GET'])]
    public function register(Request $request, EntityManagerInterface $em,
                             UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
// Extraire les données JSON de la requête
        $data = json_decode($request->getContent(), true);

        // Vérifier que les données ont bien été extraites
        if (is_null($data)) {
            return new JsonResponse(['message' => 'Données JSON invalides'], 400);
        }

        $Customer = new Customer();

        $Customer->setFirstname($data['nom']);
        $Customer->setLastname($data['prenom']);
        $Customer->setEmail($data['email']);
        $Customer->setPassword($passwordHasher->hashPassword($Customer, $data['password']));
        $Customer->setRoles(['CUSTOMER']);
        $Customer->setDateCreate();

        $em->persist($Customer);
        $em->flush();

        return new JsonResponse(['message' => 'Utilisateur enregistré avec succès'], 201);
    }
}

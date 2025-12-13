<?php

namespace App\Controller;

use App\Document\User;
use Doctrine\ODM\MongoDB\DocumentManager;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/auth')]
class AuthController extends AbstractController
{
    #[Route('/register', name: 'api_register', methods: ['POST'])]
    public function register(
        Request $request,
        DocumentManager $dm,
        UserPasswordHasherInterface $passwordHasher,
        ValidatorInterface $validator,
        JWTTokenManagerInterface $jwtManager
    ): JsonResponse {
        $payload = json_decode($request->getContent(), true) ?? [];

        $violations = $validator->validate($payload, new Assert\Collection([
            'name' => [new Assert\NotBlank(), new Assert\Length(min: 2, max: 100)],
            'email' => [new Assert\NotBlank(), new Assert\Email()],
            'password' => [new Assert\NotBlank(), new Assert\Length(min: 6)],
        ]));

        if (count($violations) > 0) {
            return $this->json([
                'errors' => array_map(static fn ($v) => $v->getPropertyPath() . ': ' . $v->getMessage(), iterator_to_array($violations)),
            ], Response::HTTP_BAD_REQUEST);
        }

        $existing = $dm->getRepository(User::class)->findOneBy(['email' => strtolower($payload['email'])]);
        if ($existing) {
            return $this->json(['message' => 'Email already registered'], Response::HTTP_CONFLICT);
        }

        $user = (new User())
            ->setName($payload['name'])
            ->setEmail($payload['email']);

        $user->setPassword($passwordHasher->hashPassword($user, $payload['password']));

        $dm->persist($user);
        $dm->flush();

        $token = $jwtManager->create($user);

        return $this->json([
            'token' => $token,
            'user' => [
                'id' => $user->getId(),
                'name' => $user->getName(),
                'email' => $user->getEmail(),
            ],
        ], Response::HTTP_CREATED);
    }
}

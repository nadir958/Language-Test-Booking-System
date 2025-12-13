<?php

namespace App\Controller;

use App\Document\User;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api')]
class UserController extends AbstractController
{
    #[Route('/me', name: 'api_me', methods: ['GET'])]
    public function me(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->json([
            'id' => $user->getId(),
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
        ]);
    }

    #[Route('/me', name: 'api_me_update', methods: ['PATCH'])]
    public function updateMe(
        Request $request,
        DocumentManager $dm,
        ValidatorInterface $validator
    ): JsonResponse {
        /** @var User $user */
        $user = $this->getUser();
        $payload = json_decode($request->getContent(), true) ?? [];

        $violations = $validator->validate($payload, new Assert\Collection([
            'name' => [new Assert\Optional([new Assert\NotBlank(), new Assert\Length(min: 2, max: 100)])],
            'email' => [new Assert\Optional([new Assert\NotBlank(), new Assert\Email()])],
        ]));

        if (count($violations) > 0) {
            return $this->json([
                'errors' => array_map(static fn ($v) => $v->getPropertyPath() . ': ' . $v->getMessage(), iterator_to_array($violations)),
            ], Response::HTTP_BAD_REQUEST);
        }

        if (isset($payload['email'])) {
            $existing = $dm->getRepository(User::class)->findOneBy(['email' => strtolower($payload['email'])]);
            if ($existing && $existing->getId() !== $user->getId()) {
                return $this->json(['message' => 'Email already in use'], Response::HTTP_CONFLICT);
            }
            $user->setEmail($payload['email']);
        }

        if (isset($payload['name'])) {
            $user->setName($payload['name']);
        }

        $dm->flush();

        return $this->json([
            'id' => $user->getId(),
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
        ]);
    }
}

<?php

namespace App\Controller;

use App\Document\Reservation;
use App\Document\Session;
use App\Document\User;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/reservations')]
class ReservationController extends AbstractController
{
    #[Route('', name: 'api_reservations_list', methods: ['GET'])]
    public function list(DocumentManager $dm): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        $reservations = $dm->getRepository(Reservation::class)
            ->createQueryBuilder()
            ->field('user')->references($user)
            ->sort('createdAt', 'desc')
            ->getQuery()
            ->execute()
            ->toArray();

        $data = array_map(function (Reservation $reservation) {
            $session = $reservation->getSession();

            return [
                'id' => $reservation->getId(),
                'session' => [
                    'id' => $session->getId(),
                    'language' => $session->getLanguage(),
                    'location' => $session->getLocation(),
                    'startAt' => $session->getStartAt()->format(DATE_ATOM),
                    'seats' => $session->getSeats(),
                ],
                'createdAt' => $reservation->getCreatedAt()->format(DATE_ATOM),
            ];
        }, $reservations);

        return $this->json(['data' => $data]);
    }

    #[Route('', name: 'api_reservations_create', methods: ['POST'])]
    public function create(
        Request $request,
        DocumentManager $dm,
        ValidatorInterface $validator
    ): JsonResponse {
        /** @var User $user */
        $user = $this->getUser();
        $payload = json_decode($request->getContent(), true) ?? [];

        $violations = $validator->validate($payload, new Assert\Collection([
            'sessionId' => [new Assert\NotBlank()],
        ]));

        if (count($violations) > 0) {
            return $this->json([
                'errors' => array_map(static fn ($v) => $v->getPropertyPath() . ': ' . $v->getMessage(), iterator_to_array($violations)),
            ], Response::HTTP_BAD_REQUEST);
        }

        /** @var Session|null $session */
        $session = $dm->find(Session::class, $payload['sessionId']);
        if (!$session) {
            return $this->json(['message' => 'Session not found'], Response::HTTP_NOT_FOUND);
        }

        $reservationRepo = $dm->getRepository(Reservation::class);

        $existing = $reservationRepo->createQueryBuilder()
            ->field('user')->references($user)
            ->field('session')->references($session)
            ->getQuery()
            ->getSingleResult();

        if ($existing) {
            return $this->json(['message' => 'Already booked'], Response::HTTP_CONFLICT);
        }

        $reservedCount = $reservationRepo->createQueryBuilder()
            ->field('session')->references($session)
            ->count()
            ->getQuery()
            ->execute();

        if ($reservedCount >= $session->getSeats()) {
            return $this->json(['message' => 'No seats available'], Response::HTTP_BAD_REQUEST);
        }

        $reservation = (new Reservation())
            ->setSession($session)
            ->setUser($user)
            ->setCreatedAt(new \DateTimeImmutable());

        $dm->persist($reservation);
        $dm->flush();

        return $this->json([
            'id' => $reservation->getId(),
            'session' => [
                'id' => $session->getId(),
                'language' => $session->getLanguage(),
                'location' => $session->getLocation(),
                'startAt' => $session->getStartAt()->format(DATE_ATOM),
                'seats' => $session->getSeats(),
            ],
            'createdAt' => $reservation->getCreatedAt()->format(DATE_ATOM),
        ], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'api_reservations_delete', methods: ['DELETE'])]
    public function cancel(string $id, DocumentManager $dm): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        /** @var Reservation|null $reservation */
        $reservation = $dm->find(Reservation::class, $id);
        if (!$reservation || $reservation->getUser()->getId() !== $user->getId()) {
            return $this->json(['message' => 'Reservation not found'], Response::HTTP_NOT_FOUND);
        }

        $dm->remove($reservation);
        $dm->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}

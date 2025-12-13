<?php

namespace App\Controller;

use App\Document\Reservation;
use App\Document\Session;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/sessions')]
class SessionController extends AbstractController
{
    #[Route('', name: 'api_sessions_list', methods: ['GET'])]
    public function list(Request $request, DocumentManager $dm): JsonResponse
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = max(1, min(100, (int) $request->query->get('limit', 10)));
        $skip = ($page - 1) * $limit;

        $qb = $dm->createQueryBuilder(Session::class)
            ->sort('startAt', 'asc')
            ->limit($limit)
            ->skip($skip);

        $sessions = $qb->getQuery()->execute()->toArray();
        $total = $dm->createQueryBuilder(Session::class)->count()->getQuery()->execute();

        $reservationRepo = $dm->getRepository(Reservation::class);

        $data = array_map(function (Session $session) use ($reservationRepo) {
            $reservedCount = $reservationRepo->createQueryBuilder()
                ->field('session')->references($session)
                ->count()
                ->getQuery()
                ->execute();

            return [
                'id' => $session->getId(),
                'language' => $session->getLanguage(),
                'location' => $session->getLocation(),
                'startAt' => $session->getStartAt()->format(DATE_ATOM),
                'seats' => $session->getSeats(),
                'seatsRemaining' => max(0, $session->getSeats() - $reservedCount),
            ];
        }, $sessions);

        return $this->json([
            'data' => $data,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
            ],
        ]);
    }

    #[Route('', name: 'api_sessions_create', methods: ['POST'])]
    public function create(Request $request, DocumentManager $dm, ValidatorInterface $validator): JsonResponse
    {
        $payload = json_decode($request->getContent(), true) ?? [];

        $violations = $validator->validate($payload, new Assert\Collection([
            'language' => [new Assert\NotBlank(), new Assert\Length(min: 1, max: 50)],
            'location' => [new Assert\NotBlank(), new Assert\Length(min: 2, max: 150)],
            'startAt' => [new Assert\NotBlank()],
            'seats' => [new Assert\NotBlank(), new Assert\Positive()],
        ]));

        if (count($violations) > 0) {
            return $this->json([
                'errors' => array_map(static fn ($v) => $v->getPropertyPath() . ': ' . $v->getMessage(), iterator_to_array($violations)),
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $startAt = new \DateTimeImmutable($payload['startAt']);
        } catch (\Exception) {
            return $this->json(['message' => 'Invalid startAt datetime'], Response::HTTP_BAD_REQUEST);
        }

        $session = (new Session())
            ->setLanguage($payload['language'])
            ->setLocation($payload['location'])
            ->setStartAt($startAt)
            ->setSeats((int) $payload['seats']);

        $dm->persist($session);
        $dm->flush();

        return $this->json([
            'id' => $session->getId(),
            'language' => $session->getLanguage(),
            'location' => $session->getLocation(),
            'startAt' => $session->getStartAt()->format(DATE_ATOM),
            'seats' => $session->getSeats(),
        ], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'api_sessions_update', methods: ['PUT', 'PATCH'])]
    public function update(string $id, Request $request, DocumentManager $dm, ValidatorInterface $validator): JsonResponse
    {
        /** @var Session|null $session */
        $session = $dm->find(Session::class, $id);
        if (!$session) {
            return $this->json(['message' => 'Session not found'], Response::HTTP_NOT_FOUND);
        }

        $payload = json_decode($request->getContent(), true) ?? [];

        $violations = $validator->validate($payload, new Assert\Collection([
            'language' => [new Assert\Optional([new Assert\NotBlank(), new Assert\Length(min: 1, max: 50)])],
            'location' => [new Assert\Optional([new Assert\NotBlank(), new Assert\Length(min: 2, max: 150)])],
            'startAt' => [new Assert\Optional([new Assert\NotBlank()])],
            'seats' => [new Assert\Optional([new Assert\NotBlank(), new Assert\Positive()])],
        ]));

        if (count($violations) > 0) {
            return $this->json([
                'errors' => array_map(static fn ($v) => $v->getPropertyPath() . ': ' . $v->getMessage(), iterator_to_array($violations)),
            ], Response::HTTP_BAD_REQUEST);
        }

        if (isset($payload['language'])) {
            $session->setLanguage($payload['language']);
        }
        if (isset($payload['location'])) {
            $session->setLocation($payload['location']);
        }
        if (isset($payload['startAt'])) {
            try {
                $session->setStartAt(new \DateTimeImmutable($payload['startAt']));
            } catch (\Exception) {
                return $this->json(['message' => 'Invalid startAt datetime'], Response::HTTP_BAD_REQUEST);
            }
        }
        if (isset($payload['seats'])) {
            $session->setSeats((int) $payload['seats']);
        }

        $dm->flush();

        return $this->json([
            'id' => $session->getId(),
            'language' => $session->getLanguage(),
            'location' => $session->getLocation(),
            'startAt' => $session->getStartAt()->format(DATE_ATOM),
            'seats' => $session->getSeats(),
        ]);
    }

    #[Route('/{id}', name: 'api_sessions_delete', methods: ['DELETE'])]
    public function delete(string $id, DocumentManager $dm): JsonResponse
    {
        /** @var Session|null $session */
        $session = $dm->find(Session::class, $id);
        if (!$session) {
            return $this->json(['message' => 'Session not found'], Response::HTTP_NOT_FOUND);
        }

        $dm->remove($session);
        $dm->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}

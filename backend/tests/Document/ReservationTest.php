<?php

namespace App\Tests\Document;

use App\Document\Reservation;
use App\Document\Session;
use App\Document\User;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class ReservationTest extends TestCase
{
    public function testReservationLinksUserAndSession(): void
    {
        $user = (new User())->setEmail('a@b.com')->setName('Test');
        $session = (new Session())->setLanguage('English')->setLocation('Paris')->setStartAt(new DateTimeImmutable())->setSeats(5);
        $createdAt = new DateTimeImmutable('2025-01-02T10:00:00Z');

        $reservation = (new Reservation())
            ->setUser($user)
            ->setSession($session)
            ->setCreatedAt($createdAt);

        $this->assertSame($user, $reservation->getUser());
        $this->assertSame($session, $reservation->getSession());
        $this->assertSame($createdAt, $reservation->getCreatedAt());
    }
}

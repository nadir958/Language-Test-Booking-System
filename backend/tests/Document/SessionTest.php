<?php

namespace App\Tests\Document;

use App\Document\Session;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class SessionTest extends TestCase
{
    public function testSessionStoresData(): void
    {
        $start = new DateTimeImmutable('2025-01-01T09:00:00Z');

        $session = (new Session())
            ->setLanguage('English')
            ->setLocation('Paris')
            ->setStartAt($start)
            ->setSeats(10);

        $this->assertSame('English', $session->getLanguage());
        $this->assertSame('Paris', $session->getLocation());
        $this->assertSame($start, $session->getStartAt());
        $this->assertSame(10, $session->getSeats());
    }
}

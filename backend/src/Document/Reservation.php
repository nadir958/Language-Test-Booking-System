<?php

namespace App\Document;

use DateTimeImmutable;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

#[ODM\Document(collection: 'reservations')]
#[ODM\Index(keys: ['user' => 'asc', 'session' => 'asc'], unique: true)]
class Reservation
{
    #[ODM\Id]
    private ?string $id = null;

    #[ODM\ReferenceOne(targetDocument: Session::class, storeAs: 'dbRef')]
    private Session $session;

    #[ODM\ReferenceOne(targetDocument: User::class, storeAs: 'dbRef')]
    private User $user;

    #[ODM\Field(type: 'date_immutable')]
    private DateTimeImmutable $createdAt;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getSession(): Session
    {
        return $this->session;
    }

    public function setSession(Session $session): self
    {
        $this->session = $session;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}

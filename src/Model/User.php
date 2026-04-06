<?php

declare(strict_types=1);

namespace App\Model;

use Buildable\SerializerBundle\Attribute\Serializable;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\Ignore;
use Symfony\Component\Serializer\Attribute\MaxDepth;
use Symfony\Component\Serializer\Attribute\SerializedName;

#[Serializable]
class User
{
    #[Groups(["user:read", "user:list"])]
    private int $id;

    #[Groups(["user:read", "user:list"])]
    private string $firstName;

    #[Groups(["user:read", "user:list"])]
    private string $lastName;

    #[Groups(["user:read"])]
    #[SerializedName("email_address")]
    private string $email;

    #[Groups(["user:read"])]
    private ?Address $address = null;

    #[Ignore]
    private string $passwordHash = "";

    #[Groups(["user:read"])]
    private bool $active = true;

    public function __construct(
        int $id,
        string $firstName,
        string $lastName,
        string $email,
    ) {
        $this->id = $id;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getAddress(): ?Address
    {
        return $this->address;
    }

    public function setAddress(?Address $address): void
    {
        $this->address = $address;
    }

    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    public function setPasswordHash(string $hash): void
    {
        $this->passwordHash = $hash;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }
}

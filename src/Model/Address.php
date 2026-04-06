<?php

declare(strict_types=1);

namespace App\Model;

use Buildable\SerializerBundle\Attribute\Serializable;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;

#[Serializable]
class Address
{
    #[Groups(["address:read", "user:read"])]
    public string $street;

    #[Groups(["address:read", "user:read"])]
    public string $city;

    #[Groups(["address:read", "user:read"])]
    #[SerializedName("postal_code")]
    public string $postalCode;

    #[Groups(["address:read", "user:read"])]
    public string $country;

    public function __construct(
        string $street,
        string $city,
        string $postalCode,
        string $country,
    ) {
        $this->street = $street;
        $this->city = $city;
        $this->postalCode = $postalCode;
        $this->country = $country;
    }

    public function getStreet(): string
    {
        return $this->street;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    public function getCountry(): string
    {
        return $this->country;
    }
}

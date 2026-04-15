<?php

namespace App\Model;

use Symfony\Component\Serializer\Attribute\Groups;

final class UsersCollection extends AbstractCollection
{
    #[Groups('user:list')]
    protected iterable $users;

    public function __construct(
        iterable $users
    ) {
        parent::__construct('users', $users);
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function getUsers(): iterable
    {
        return $this->users;
    }
}

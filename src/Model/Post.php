<?php

declare(strict_types=1);

namespace App\Model;

use DateTimeImmutable;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\MaxDepth;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

class Post
{
    #[Groups(["post:read", "post:list"])]
    private int $id;

    #[Groups(["post:read", "post:list"])]
    private string $title;

    #[Groups(["post:read"])]
    private string $content;

    #[Groups(["post:read", "post:list"])]
    #[MaxDepth(1)]
    private User $author;

    #[
        Groups(["post:read", "post:list"]),
        Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d'])
    ]
    private DateTimeImmutable $createdAt;

    public function __construct(
        int $id,
        string $title,
        string $content,
        User $author,
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->content = $content;
        $this->author = $author;
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getAuthor(): User
    {
        return $this->author;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}

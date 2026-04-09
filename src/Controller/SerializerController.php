<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\Address;
use App\Model\Post;
use App\Model\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[Route("/api", name: "api_")]
class SerializerController extends AbstractController
{
    public function __construct(
        private readonly SerializerInterface $serializer,
    ) {}

    /**
     * Returns a full user object with all groups enabled.
     * Demonstrates: nested object (Address), collection (Posts), Groups, SerializedName, Ignore, MaxDepth.
     *
     * GET /api/user
     */
    #[Route("/user", name: "user", methods: ["GET"])]
    public function user(): JsonResponse
    {
        $user = $this->createSampleUser();

        $data = $this->serializer->normalize($user, null, [
            "groups" => ["user:read"],
        ]);

        return $this->json([
            "description" =>
                'Full user with group "user:read". Note: passwordHash is #[Ignore]d, email uses #[SerializedName("email_address")], address uses #[MaxDepth(2)].',
            "data" => $data,
        ]);
    }

    /**
     * Returns a list-safe subset of user fields.
     * Demonstrates: group filtering - only user:list fields are included.
     *
     * GET /api/user/list
     */
    #[Route("/user/list", name: "user_list", methods: ["GET"])]
    public function userList(): JsonResponse
    {
        $users = [
            $this->createSampleUser(1, "Alice", "Smith", "alice@example.com"),
            $this->createSampleUser(2, "Bob", "Jones", "bob@example.com"),
            $this->createSampleUser(3, "Carol", "White", "carol@example.com"),
        ];

        $data = $this->serializer->normalize($users, null, [
            "groups" => ["user:list"],
        ]);

        return $this->json([
            "description" =>
                'User list with group "user:list". Only id, firstName, lastName, createdAt are included.',
            "data" => $data,
        ]);
    }

    /**
     * Returns a post with its nested author.
     * Demonstrates: nested normalization chain (Post -> User), string[] collection (tags), MaxDepth on author.
     *
     * GET /api/post
     */
    #[Route("/post", name: "post", methods: ["GET"])]
    public function post(): JsonResponse
    {
        $user = $this->createSampleUser(withAddress: true);
        $post = $this->createSamplePost($user);

        $start = microtime(true);
        for ($i = 0; $i < 200_000; $i++) {
            $data = $this->serializer->normalize($post, null, [
                "groups" => ["post:read", "user:list"],
            ]);
        }
        // dd($this->serializer);
        $elapsed = microtime(true) - $start;
        dd("Time: " . round($elapsed * 1000) . " ms", $data);

        return $this->json($data);
    }

    /**
     * Returns a list of posts (summary view).
     * Demonstrates: post:list group - only id, title, publishedAt, author, tags.
     *
     * GET /api/posts
     */
    #[Route("/posts", name: "posts", methods: ["GET"])]
    public function posts(): JsonResponse
    {
        $alice = $this->createSampleUser(
            1,
            "Alice",
            "Smith",
            "alice@example.com",
        );
        $bob = $this->createSampleUser(2, "Bob", "Jones", "bob@example.com");

        $posts = [
            $this->createSamplePost($alice, 1, "Getting Started with Symfony", [
                "symfony",
                "php",
            ]),
            $this->createSamplePost($bob, 2, "Optimizing Serialization", [
                "performance",
                "serializer",
            ]),
            $this->createSamplePost($alice, 3, "Understanding Normalizers", [
                "symfony",
                "serializer",
                "normalizer",
            ]),
        ];

        $data = $this->serializer->normalize($posts, null, [
            "groups" => ["post:list", "user:list"],
        ]);

        return $this->json([
            "description" =>
                'Post list with groups "post:list" + "user:list". Content field is excluded.',
            "data" => $data,
        ]);
    }

    /**
     * Demonstrates skip_null_values context option.
     * User without an address - null address field is omitted from output.
     *
     * GET /api/user/sparse
     */
    #[Route("/user/sparse", name: "user_sparse", methods: ["GET"])]
    public function userSparse(): JsonResponse
    {
        $user = $this->createSampleUser(
            99,
            "Sparse",
            "User",
            "sparse@example.com",
            withAddress: false,
        );

        $withNulls = $this->serializer->normalize($user, null, [
            "groups" => ["user:read"],
        ]);

        $withoutNulls = $this->serializer->normalize($user, null, [
            "groups" => ["user:read"],
            "skip_null_values" => true,
        ]);

        return $this->json([
            "description" =>
                'Same user normalized with and without skip_null_values. The "address" key vanishes when the option is enabled.',
            "with_nulls" => $withNulls,
            "without_nulls" => $withoutNulls,
        ]);
    }

    /**
     * Demonstrates circular reference handling.
     * User has posts; posts reference back to the same user.
     *
     * GET /api/circular
     */
    #[Route("/circular", name: "circular", methods: ["GET"])]
    public function circular(): JsonResponse
    {
        $user = $this->createSampleUser();
        $post = $this->createSamplePost($user);
        $user->addPost($post);

        $data = $this->serializer->normalize($user, null, [
            "groups" => ["user:read", "post:list"],
            "circular_reference_handler" => static function (
                object $obj,
            ): string {
                $ref = new \ReflectionClass($obj);
                return sprintf(
                    "[circular:%s#%d]",
                    $ref->getShortName(),
                    spl_object_id($obj),
                );
            },
        ]);

        return $this->json([
            "description" =>
                "User -> posts -> author circular reference resolved by a custom handler that emits a placeholder string.",
            "data" => $data,
        ]);
    }

    // -------------------------------------------------------------------------
    // Fixtures
    // -------------------------------------------------------------------------

    private function createSampleUser(
        int $id = 1,
        string $firstName = "Jane",
        string $lastName = "Doe",
        string $email = "jane.doe@example.com",
        bool $withAddress = true,
    ): User {
        $user = new User($id, $firstName, $lastName, $email);
        $user->setPasswordHash('$2y$13$REDACTED');
        $user->setActive(true);

        if ($withAddress) {
            $user->setAddress(
                new Address(
                    street: "123 Main Street",
                    city: "Springfield",
                    postalCode: "12345",
                    country: "US",
                ),
            );
        }

        return $user;
    }

    private function createSamplePost(
        User $author,
        int $id = 1,
        string $title = "Hello, Buildable Serializer!",
    ): Post {
        return new Post(
            id: $id,
            title: $title,
            content: "This post was normalized by a build-time generated normalizer - no reflection at runtime!",
            author: $author,
        );
    }
}

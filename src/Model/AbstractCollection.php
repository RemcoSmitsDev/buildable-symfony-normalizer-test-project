<?php

namespace App\Model;

use Countable;
use Exception;
use ReflectionClass;
use Symfony\Component\Serializer\Attribute\Groups;
use Traversable;

abstract class AbstractCollection
{
    #[Groups('user:list')]
    protected int $total;

    protected string $name;

    public function __construct(string $name, iterable $items)
    {
        $this->name = $name;
        $this->setItems($items);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getItems(): iterable
    {
        return $this->{$this->name};
    }

    public function setItems(iterable $items): self
    {
        $this->{$this->name} = $items;

        $this->total = $this->getItemsCount();

        return $this;
    }

    protected function getItemsCount(): int
    {
        $items = $this->getItems();

        if (is_array($items)) {
            return count($items);
        }

        if ($items instanceof Countable) {
            return count($items);
        }

        if ($items instanceof Traversable) {
            // Attempt to clone to avoid disturbing original
            $copy = $items;

            if (is_object($items) && (new ReflectionClass($items))->isCloneable()) {
                $copy = clone $items;
            }

            return iterator_count($copy);
        }

        /** @phpstan-ignore-next-line Cannot be reached but here for static analysis. */
        throw new Exception('Unable to count items: not an array, Countable, or Traversable.');
    }
}

<?php

declare(strict_types=1);

namespace Premier\Identifier;

use InvalidArgumentException;
use JsonSerializable;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use function get_debug_type;
use function sprintf;

/**
 * @psalm-immutable
 */
abstract class Identifier implements JsonSerializable
{
    private string $uuid;

    final private function __construct(string | UuidInterface $uuid)
    {
        $this->uuid = match (true) {
            \is_string($uuid) && self::isValid($uuid) => $uuid,
            $uuid instanceof UuidInterface => $uuid->toString(),
            default => throw new InvalidArgumentException(sprintf('"%s" is not valid uuid.', $uuid)),
        };
    }

    final public function __toString(): string
    {
        return $this->uuid;
    }

    final public function toString(): string
    {
        return $this->uuid;
    }

    /**
     * {@inheritDoc}
     */
    final public function jsonSerialize(): string
    {
        return $this->uuid;
    }

    /**
     * @template T of Identifier
     *
     * @psalm-param class-string<T> $class
     */
    final public static function fromClass(string $class, string | UuidInterface $uuid): static
    {
        /** @var callable $callable */
        $callable = $class.'::'.(\is_string($uuid) ? 'fromString' : 'fromUuid');
        $identifier = $callable($uuid);

        \assert($identifier instanceof $class);

        /** @phpstan-ignore-next-line */
        return $identifier;
    }

    /**
     * @psalm-mutation-free
     */
    final public static function same(?self $left, ?self $right): bool
    {
        if (null === $left || null === $right) {
            return false;
        }

        if ($left::class !== $right::class) {
            return false;
        }

        return $left->equals($right);
    }

    final public static function generate(): static
    {
        return new static(Uuid::uuid6());
    }

    final public static function fromString(string $uuid): static
    {
        return new static($uuid);
    }

    final public static function fromAny(mixed $any): static
    {
        if ($any instanceof static) {
            return $any;
        }

        if ($any instanceof UuidInterface) {
            return static::fromUuid($any);
        }

        if (\is_string($any)) {
            return static::fromString($any);
        }

        throw new InvalidArgumentException('Unexpected any: '.get_debug_type($any));
    }

    final public static function fromUuid(UuidInterface $uuid): static
    {
        return new static($uuid);
    }

    final public static function isValid(string $uuid): bool
    {
        return Uuid::isValid($uuid);
    }

    final public function toUuid(): UuidInterface
    {
        return Uuid::fromString($this->uuid);
    }

    final public function equals(mixed $identifier): bool
    {
        if ($identifier instanceof static || $identifier instanceof UuidInterface) {
            return $this->uuid === $identifier->toString();
        }

        if (\is_string($identifier)) {
            return $this->uuid === $identifier;
        }

        return false;
    }
}

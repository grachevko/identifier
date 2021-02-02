<?php

declare(strict_types=1);

namespace Premier\Identifier;

use JsonSerializable;
use LogicException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use function get_debug_type;

/**
 * @psalm-immutable
 */
abstract class Identifier implements JsonSerializable
{
    private UuidInterface $uuid;

    final private function __construct(UuidInterface $uuid)
    {
        $this->uuid = $uuid;
    }

    final public function __toString(): string
    {
        return $this->toString();
    }

    final public function toString(): string
    {
        return $this->uuid->toString();
    }

    /**
     * {@inheritDoc}
     */
    final public function jsonSerialize(): string
    {
        return $this->toString();
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

        /** @phpstan-ignore-next-line  */
        return $identifier;
    }

    /**
     * @psalm-mutation-free
     */
    final public static function same(?self $left, ?self $right): bool
    {
        return (null === $left && null === $right)
            || (null === $left ? null : $left->toString()) === (null === $right ? null : $right->toString());
    }

    final public static function generate(): static
    {
        return new static(Uuid::uuid6());
    }

    public static function fromUuidOrNull(?UuidInterface $uuid): ?static
    {
        return null === $uuid ? null : self::fromUuid($uuid);
    }

    final public static function fromString(string $uuid): static
    {
        return new static(Uuid::fromString($uuid));
    }

    public static function fromAny(mixed $any): static
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

        throw new LogicException('Unexpected any: '.get_debug_type($any));
    }

    final public static function fromUuid(UuidInterface $uuid): static
    {
        return new static($uuid);
    }

    public static function isValid(string $uuid): bool
    {
        return Uuid::isValid($uuid);
    }

    final public function toUuid(): UuidInterface
    {
        return $this->uuid;
    }

    public function equal(?self $identifier): bool
    {
        return null !== $identifier && $identifier->toString() === $this->toString();
    }
}

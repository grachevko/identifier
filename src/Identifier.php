<?php

declare(strict_types=1);

namespace Premier\Identifier;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @psalm-immutable
 */
abstract class Identifier implements \JsonSerializable
{
    private string $uuid;

    final public function __construct(string | UuidInterface $uuid)
    {
        $this->uuid = match (true) {
            \is_string($uuid) && Uuid::isValid($uuid) => $uuid,
            $uuid instanceof UuidInterface => $uuid->toString(),
            default => throw new \InvalidArgumentException(sprintf('"%s" is not valid uuid.', $uuid)),
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

    final public static function generate(): static
    {
        return new static(Uuid::uuid6());
    }

    /**
     * @psalm-pure
     */
    final public static function from(string | UuidInterface | self | null ...$values): static
    {
        return self::try(...$values) ?? throw new \InvalidArgumentException('Expect at least one non nullable value');
    }

    /**
     * @psalm-pure
     */
    final public static function try(string | UuidInterface | self | null ...$values): ?static
    {
        foreach ($values as $value) {
            if (null === $value) {
                continue;
            }

            return match (true) {
                \is_string($value) || $value instanceof UuidInterface => new static($value),
                default => new static($value->toUuid()),
            };
        }

        return null;
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

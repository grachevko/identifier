<?php

declare(strict_types=1);

namespace Premier\Identifier;

use Ramsey\Uuid\Type\Hexadecimal;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @psalm-immutable
 */
abstract class Identifier implements \JsonSerializable
{
    /**
     * @var array<class-string<Identifier>, string>
     */
    public static array $map = [];

    private string $uuid;

    final public function __construct(string | UuidInterface $uuid)
    {
        if (\is_string($uuid) && !Uuid::isValid($uuid)) {
            throw new \InvalidArgumentException(sprintf('"%s" is not valid uuid.', $uuid));
        }

        if ($uuid instanceof UuidInterface) {
            $uuid = $uuid->toString();
        }

        $node = substr($uuid, 24);

        if (!\in_array($node, self::$map, true)) {
            throw new \InvalidArgumentException(sprintf('Hexadecimal Node "%s" not found in map: %s', $node, http_build_query(self::$map)));
        }

        $this->uuid = $uuid;
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
        return new static(Uuid::uuid6(new Hexadecimal(self::$map[static::class])));
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

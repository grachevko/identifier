<?php

declare(strict_types=1);

namespace Premier\Identifier\Doctrine;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use Premier\Identifier\Identifier;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class IdentifierArrayType extends Type
{
    /**
     * {@inheritdoc}
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if (null === $value) {
            return null;
        }

        if (!\is_array($value)) {
            throw ConversionException::conversionFailed($value, $this->getName());
        }

        $value = array_map(static fn (Identifier $identifier): string => $identifier->toString(), $value);

        try {
            /** @var string $encoded */
            $encoded = json_encode($value, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw ConversionException::conversionFailedSerialization($value, 'json', $e->getMessage());
        }

        return $encoded;
    }

    /**
     * {@inheritdoc}
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): array
    {
        if (null === $value) {
            return [];
        }

        try {
            $value = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw ConversionException::conversionFailed($value, $this->getName(), $e);
        }

        $map = array_flip(Identifier::$map);

        return array_map(
            function (string $id) use ($map, $value): Identifier {
                $node = substr($id, 24);
                /** @var class-string<Identifier> $class */
                $class = $map[$node] ?? throw ConversionException::conversionFailed($value, $this->getName());

                return new $class($id);
            },
            $value
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getJsonTypeDeclarationSQL($column);
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'identifiers';
    }

    /**
     * {@inheritdoc}
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}

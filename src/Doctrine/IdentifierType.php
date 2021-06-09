<?php

declare(strict_types=1);

namespace Premier\Identifier\Doctrine;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use Premier\Identifier\Identifier;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class IdentifierType extends Type
{
    /**
     * {@inheritdoc}
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if (null === $value || '' === $value) {
            return null;
        }

        if (\is_string($value) && Uuid::isValid($value)) {
            return $value;
        }

        if ($value instanceof Identifier || $value instanceof UuidInterface) {
            return $value->toString();
        }

        throw ConversionException::conversionFailed($value, $this->getName());
    }

    /**
     * {@inheritdoc}
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?Identifier
    {
        if (null === $value || '' === $value) {
            return null;
        }

        $node = substr($value, 24);

        /** @var class-string<Identifier> $class */
        $class = array_flip(Identifier::$map)[$node] ?? throw ConversionException::conversionFailed($value, $this->getName());

        return new $class($value);
    }

    /**
     * {@inheritdoc}
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getGuidTypeDeclarationSQL($column);
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'identifier';
    }

    /**
     * {@inheritdoc}
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}

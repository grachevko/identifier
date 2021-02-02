<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Premier\Identifier\Identifier;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class IdentifierTest extends TestCase
{
    private const UUID = '1eb65a11-b71f-67f0-baa3-7a5ffee21f49';

    public function testGenerate(): void
    {
        self::assertInstanceOf(TestId::class, TestId::generate());
    }

    public function testFromString(): void
    {
        $id = TestId::fromString(self::UUID);

        self::assertInstanceOf(TestId::class, $id);
        self::assertSame(self::UUID, $id->toString());
    }

    public function testFromUuid(): void
    {
        $uuid = Uuid::fromString(self::UUID);
        $id = TestId::fromUuid($uuid);

        self::assertInstanceOf(TestId::class, $id);
        self::assertInstanceOf(UuidInterface::class, $id->toUuid());
        self::assertSame(self::UUID, $id->toString());
    }
}

final class TestId extends Identifier
{
}

<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Premier\Identifier\Identifier;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class IdentifierTest extends TestCase
{
    private const UUID = '1eb65a11-b71f-67f0-baa3-7a5ffee21f49';
    private const UUID2 = '1eb7a9e2-7af4-6dd0-8451-0242ac1f000b';

    public function testGenerate(): void
    {
        self::assertInstanceOf(TestId::class, TestId::generate());
    }

    /**
     * @dataProvider strings
     */
    public function testFromString(string $uuid): void
    {
        $id = TestId::fromString($uuid);

        self::assertInstanceOf(TestId::class, $id);
        self::assertSame($uuid, $id->toString());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('"bla" is not valid uuid.');

        TestId::fromString('bla');
    }

    public function testFromStrings(): void
    {
        $ids = TestId::fromStrings([self::UUID, self::UUID2]);

        self::assertCount(2, $ids);

        self::assertInstanceOf(TestId::class, $ids[0]);
        self::assertSame(self::UUID, $ids[0]->toString());
        self::assertInstanceOf(TestId::class, $ids[1]);
        self::assertSame(self::UUID2, $ids[1]->toString());
    }

    /**
     * @dataProvider uuids
     */
    public function testFromUuid(UuidInterface $uuid): void
    {
        $id = TestId::fromUuid($uuid);

        self::assertInstanceOf(TestId::class, $id);
        self::assertSame($uuid->toString(), $id->toString());

        self::assertFalse($id->equals(TestId::generate()));
    }

    public function testFromUuids(): void
    {
        $ids = TestId::fromUuids([Uuid::fromString(self::UUID), Uuid::fromString(self::UUID2)]);

        self::assertCount(2, $ids);

        self::assertInstanceOf(TestId::class, $ids[0]);
        self::assertSame(self::UUID, $ids[0]->toString());
        self::assertInstanceOf(TestId::class, $ids[1]);
        self::assertSame(self::UUID2, $ids[1]->toString());
    }

    /**
     * @dataProvider strings
     * @dataProvider uuids
     */
    public function testFrom(string | UuidInterface $uuid): void
    {
        $id = TestId::from($uuid);

        self::assertInstanceOf(TestId::class, $id);
        self::assertSame((string) $uuid, $id->toString());
    }

    public function testIsValid(): void
    {
        self::assertTrue(Identifier::isValid(self::UUID));
        self::assertFalse(Identifier::isValid('bla'));
    }

    public function testFromAny(): void
    {
        self::assertInstanceOf(TestId::class, TestId::fromAny(self::UUID));
        self::assertInstanceOf(TestId::class, TestId::fromAny(Uuid::uuid6()));
        self::assertInstanceOf(TestId::class, TestId::fromAny(TestId::generate()));

        $id = Test2Id::fromAny(TestId::fromString(self::UUID));
        self::assertInstanceOf(Test2Id::class, $id);
        self::assertSame(self::UUID, $id->toString());
    }

    public function testSame(): void
    {
        self::assertFalse(Identifier::same(TestId::generate(), TestId::generate()));

        $id = TestId::generate();
        self::assertTrue(Identifier::same($id, $id));

        self::assertTrue(Identifier::same(TestId::fromString(self::UUID), TestId::fromString(self::UUID)));
        self::assertFalse(Identifier::same(TestId::fromString(self::UUID), Test2Id::fromString(self::UUID)));
    }

    public function testEquals(): void
    {
        $id = TestId::generate();

        self::assertFalse($id->equals(TestId::generate()));

        self::assertTrue($id->equals($id->toString()));
        self::assertFalse($id->equals(Test2Id::fromString($id->toString())));
    }

    public function testJsonSerialize(): void
    {
        $id = TestId::generate();

        self::assertSame($id->toString(), $id->jsonSerialize());
        self::assertSame(sprintf('"%s"', $id->toString()), json_encode($id, JSON_THROW_ON_ERROR));
    }

    public function testSerialize(): void
    {
        $id = TestId::generate();

        self::assertTrue($id->equals(unserialize(serialize($id))));
    }

    public function strings(): Generator
    {
        yield [self::UUID];
        yield [self::UUID2];
    }

    public function uuids(): Generator
    {
        yield [Uuid::fromString(self::UUID)];
        yield [Uuid::fromString(self::UUID2)];
    }
}

/**
 * @psalm-immutable
 */
final class TestId extends Identifier
{
}

/**
 * @psalm-immutable
 */
final class Test2Id extends Identifier
{
}

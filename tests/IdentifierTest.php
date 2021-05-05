<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Premier\Identifier\Identifier;
use Ramsey\Uuid\Uuid;

final class IdentifierTest extends TestCase
{
    private const UUID = '1eb65a11-b71f-67f0-baa3-7a5ffee21f49';
    private const UUID2 = '1eb7a9e2-7af4-6dd0-8451-0242ac1f000b';

    public function testGenerate(): void
    {
        self::assertInstanceOf(TestId::class, TestId::generate());
    }

    /**
     * @dataProvider values
     */
    public function testFrom(array $values, string $expected): void
    {
        $id = TestId::from(...$values);

        self::assertInstanceOf(TestId::class, $id);
        self::assertSame($expected, $id->toString());
    }

    public function testFromNull(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expect at least one non nullable value');

        /** @phpstan-ignore-next-line  */
        TestId::from(null);
    }

    /**
     * @dataProvider values
     */
    public function testTry(array $values, ?string $expected): void
    {
        $id = TestId::try(...$values);

        self::assertInstanceOf(TestId::class, $id);
        self::assertSame($expected, $id->toString());
    }

    public function testTryNull(): void
    {
        $id = TestId::try(null);

        self::assertNull($id);
    }

    public function values(): Generator
    {
        yield [[self::UUID], self::UUID];
        yield [[null, self::UUID], self::UUID];
        yield [[self::UUID2], self::UUID2];
        yield [[null, null, self::UUID2, self::UUID], self::UUID2];
        yield [[Uuid::fromString(self::UUID)], self::UUID];
        yield [[Uuid::fromString(self::UUID2)], self::UUID2];
        yield [[null, Uuid::fromString(self::UUID2)], self::UUID2];
        yield [[TestId::from(self::UUID2)], self::UUID2];
    }

    public function testFromFail(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('"bla" is not valid uuid.');

        /** @phpstan-ignore-next-line  */
        TestId::from('bla');
    }

    public function testEquals(): void
    {
        $id = TestId::generate();

        self::assertFalse($id->equals(TestId::generate()));

        self::assertTrue($id->equals($id->toString()));
        self::assertFalse($id->equals(Test2Id::from($id->toString())));
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

<?php

declare(strict_types=1);

namespace LaminasTest\Http\Header;

use Laminas\Http\Header\Exception\InvalidArgumentException;
use Laminas\Http\Header\Expect;
use Laminas\Http\Header\HeaderInterface;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class ExpectTest extends TestCase
{
    public function testExpectFromStringCreatesValidExpectHeader(): void
    {
        $expectHeader = Expect::fromString('Expect: xxx');
        $this->assertInstanceOf(HeaderInterface::class, $expectHeader);
        $this->assertInstanceOf(Expect::class, $expectHeader);
    }

    public function testExpectGetFieldNameReturnsHeaderName(): void
    {
        $expectHeader = new Expect();
        $this->assertEquals('Expect', $expectHeader->getFieldName());
    }

    public function testExpectGetFieldValueReturnsProperValue(): void
    {
        $this->markTestIncomplete('Expect needs to be completed');

        $expectHeader = new Expect();
        $this->assertEquals('xxx', $expectHeader->getFieldValue());
    }

    public function testExpectToStringReturnsHeaderFormattedString(): void
    {
        $this->markTestIncomplete('Expect needs to be completed');

        $expectHeader = new Expect();

        // @todo set some values, then test output
        $this->assertEmpty('Expect: xxx', $expectHeader->toString());
    }

    /** Implementation specific tests here */
    /**
     * @see http://en.wikipedia.org/wiki/HTTP_response_splitting
     */
    #[Group('ZF2015-04')]
    public function testPreventsCRLFAttackViaFromString(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Expect::fromString("Expect: xxx\r\n\r\nevilContent");
    }

    /**
     * @see http://en.wikipedia.org/wiki/HTTP_response_splitting
     */
    #[Group('ZF2015-04')]
    public function testPreventsCRLFAttackViaConstructor(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Expect("xxx\r\n\r\nevilContent");
    }
}

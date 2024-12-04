<?php

declare(strict_types=1);

namespace LaminasTest\Http\Header;

use Laminas\Http\Header\Exception\InvalidArgumentException;
use Laminas\Http\Header\HeaderInterface;
use Laminas\Http\Header\MaxForwards;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class MaxForwardsTest extends TestCase
{
    public function testMaxForwardsFromStringCreatesValidMaxForwardsHeader(): void
    {
        $maxForwardsHeader = MaxForwards::fromString('Max-Forwards: xxx');
        $this->assertInstanceOf(HeaderInterface::class, $maxForwardsHeader);
        $this->assertInstanceOf(MaxForwards::class, $maxForwardsHeader);
    }

    public function testMaxForwardsGetFieldNameReturnsHeaderName(): void
    {
        $maxForwardsHeader = new MaxForwards();
        $this->assertEquals('Max-Forwards', $maxForwardsHeader->getFieldName());
    }

    public function testMaxForwardsGetFieldValueReturnsProperValue(): void
    {
        $this->markTestIncomplete('MaxForwards needs to be completed');

        $maxForwardsHeader = new MaxForwards();
        $this->assertEquals('xxx', $maxForwardsHeader->getFieldValue());
    }

    public function testMaxForwardsToStringReturnsHeaderFormattedString(): void
    {
        $this->markTestIncomplete('MaxForwards needs to be completed');

        $maxForwardsHeader = new MaxForwards();

        // @todo set some values, then test output
        $this->assertEmpty('Max-Forwards: xxx', $maxForwardsHeader->toString());
    }

    /** Implementation specific tests here */
    /**
     * @see http://en.wikipedia.org/wiki/HTTP_response_splitting
     */
    #[Group('ZF2015-04')]
    public function testPreventsCRLFAttackViaFromString(): void
    {
        $this->expectException(InvalidArgumentException::class);
        MaxForwards::fromString("Max-Forwards: xxx\r\n\r\nevilContent");
    }

    /**
     * @see http://en.wikipedia.org/wiki/HTTP_response_splitting
     */
    #[Group('ZF2015-04')]
    public function testPreventsCRLFAttackViaConstructorValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new MaxForwards("xxx\r\n\r\nevilContent");
    }
}

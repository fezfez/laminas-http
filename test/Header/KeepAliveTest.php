<?php

declare(strict_types=1);

namespace LaminasTest\Http\Header;

use Laminas\Http\Header\Exception\InvalidArgumentException;
use Laminas\Http\Header\HeaderInterface;
use Laminas\Http\Header\KeepAlive;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class KeepAliveTest extends TestCase
{
    public function testKeepAliveFromStringCreatesValidKeepAliveHeader(): void
    {
        $keepAliveHeader = KeepAlive::fromString('Keep-Alive: xxx');
        $this->assertInstanceOf(HeaderInterface::class, $keepAliveHeader);
        $this->assertInstanceOf(KeepAlive::class, $keepAliveHeader);
    }

    public function testKeepAliveGetFieldNameReturnsHeaderName(): void
    {
        $keepAliveHeader = new KeepAlive();
        $this->assertEquals('Keep-Alive', $keepAliveHeader->getFieldName());
    }

    public function testKeepAliveGetFieldValueReturnsProperValue(): void
    {
        $this->markTestIncomplete('KeepAlive needs to be completed');

        $keepAliveHeader = new KeepAlive();
        $this->assertEquals('xxx', $keepAliveHeader->getFieldValue());
    }

    public function testKeepAliveToStringReturnsHeaderFormattedString(): void
    {
        $this->markTestIncomplete('KeepAlive needs to be completed');

        $keepAliveHeader = new KeepAlive();

        // @todo set some values, then test output
        $this->assertEmpty('Keep-Alive: xxx', $keepAliveHeader->toString());
    }

    /** Implementation specific tests here */
    /**
     * @see http://en.wikipedia.org/wiki/HTTP_response_splitting
     */
    #[Group('ZF2015-04')]
    public function testPreventsCRLFAttackViaFromString(): void
    {
        $this->expectException(InvalidArgumentException::class);
        KeepAlive::fromString("Keep-Alive: xxx\r\n\r\nevilContent");
    }

    /**
     * @see http://en.wikipedia.org/wiki/HTTP_response_splitting
     */
    #[Group('ZF2015-04')]
    public function testPreventsCRLFAttackViaConstructor(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new KeepAlive("xxx\r\n\r\nevilContent");
    }
}

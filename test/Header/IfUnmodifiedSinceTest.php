<?php

declare(strict_types=1);

namespace LaminasTest\Http\Header;

use Laminas\Http\Header\Exception\InvalidArgumentException;
use Laminas\Http\Header\HeaderInterface;
use Laminas\Http\Header\IfUnmodifiedSince;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class IfUnmodifiedSinceTest extends TestCase
{
    public function testIfUnmodifiedSinceFromStringCreatesValidIfUnmodifiedSinceHeader(): void
    {
        $ifUnmodifiedSinceHeader = IfUnmodifiedSince::fromString('If-Unmodified-Since: Sun, 06 Nov 1994 08:49:37 GMT');
        $this->assertInstanceOf(HeaderInterface::class, $ifUnmodifiedSinceHeader);
        $this->assertInstanceOf(IfUnmodifiedSince::class, $ifUnmodifiedSinceHeader);
    }

    public function testIfUnmodifiedSinceGetFieldNameReturnsHeaderName(): void
    {
        $ifUnmodifiedSinceHeader = new IfUnmodifiedSince();
        $this->assertEquals('If-Unmodified-Since', $ifUnmodifiedSinceHeader->getFieldName());
    }

    public function testIfUnmodifiedSinceGetFieldValueReturnsProperValue(): void
    {
        $ifUnmodifiedSinceHeader = new IfUnmodifiedSince();
        $ifUnmodifiedSinceHeader->setDate('Sun, 06 Nov 1994 08:49:37 GMT');
        $this->assertEquals('Sun, 06 Nov 1994 08:49:37 GMT', $ifUnmodifiedSinceHeader->getFieldValue());
    }

    public function testIfUnmodifiedSinceToStringReturnsHeaderFormattedString(): void
    {
        $ifUnmodifiedSinceHeader = new IfUnmodifiedSince();
        $ifUnmodifiedSinceHeader->setDate('Sun, 06 Nov 1994 08:49:37 GMT');
        $this->assertEquals('If-Unmodified-Since: Sun, 06 Nov 1994 08:49:37 GMT', $ifUnmodifiedSinceHeader->toString());
    }

    /**
     * Implementation specific tests are covered by DateTest
     *
     * @see LaminasTest\Http\Header\DateTest
     */
    /**
     * @see http://en.wikipedia.org/wiki/HTTP_response_splitting
     */
    #[Group('ZF2015-04')]
    public function testCRLFAttack(): void
    {
        $this->expectException(InvalidArgumentException::class);
        IfUnmodifiedSince::fromString(
            "If-Unmodified-Since: Sun, 06 Nov 1994 08:49:37 GMT\r\n\r\nevilContent"
        );
    }
}

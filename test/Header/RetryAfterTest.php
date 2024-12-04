<?php

declare(strict_types=1);

namespace LaminasTest\Http\Header;

use Laminas\Http\Header\Exception\InvalidArgumentException;
use Laminas\Http\Header\HeaderInterface;
use Laminas\Http\Header\RetryAfter;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class RetryAfterTest extends TestCase
{
    public function testRetryAfterFromStringCreatesValidRetryAfterHeader(): void
    {
        $retryAfterHeader = RetryAfter::fromString('Retry-After: 10');
        $this->assertInstanceOf(HeaderInterface::class, $retryAfterHeader);
        $this->assertInstanceOf(RetryAfter::class, $retryAfterHeader);
        $this->assertEquals('10', $retryAfterHeader->getDeltaSeconds());
    }

    public function testRetryAfterFromStringCreatesValidRetryAfterHeaderFromDate(): void
    {
        $retryAfterHeader = RetryAfter::fromString('Retry-After: Sun, 06 Nov 1994 08:49:37 GMT');
        $this->assertEquals('Sun, 06 Nov 1994 08:49:37 GMT', $retryAfterHeader->getDate());
    }

    public function testRetryAfterGetFieldNameReturnsHeaderName(): void
    {
        $retryAfterHeader = new RetryAfter();
        $this->assertEquals('Retry-After', $retryAfterHeader->getFieldName());
    }

    public function testRetryAfterGetFieldValueReturnsProperValue(): void
    {
        $retryAfterHeader = new RetryAfter();
        $retryAfterHeader->setDeltaSeconds(3600);
        $this->assertEquals('3600', $retryAfterHeader->getFieldValue());
        $retryAfterHeader->setDate('Sun, 06 Nov 1994 08:49:37 GMT');
        $this->assertEquals('Sun, 06 Nov 1994 08:49:37 GMT', $retryAfterHeader->getFieldValue());
    }

    public function testRetryAfterToStringReturnsHeaderFormattedString(): void
    {
        $retryAfterHeader = new RetryAfter();

        $retryAfterHeader->setDeltaSeconds(3600);
        $this->assertEquals('Retry-After: 3600', $retryAfterHeader->toString());

        $retryAfterHeader->setDate('Sun, 06 Nov 1994 08:49:37 GMT');
        $this->assertEquals('Retry-After: Sun, 06 Nov 1994 08:49:37 GMT', $retryAfterHeader->toString());
    }

    /**
     * @see http://en.wikipedia.org/wiki/HTTP_response_splitting
     */
    #[Group('ZF2015-04')]
    public function testPreventsCRLFAttackViaFromString(): void
    {
        $this->expectException(InvalidArgumentException::class);
        RetryAfter::fromString("Retry-After: 10\r\n\r\nevilContent");
    }
}

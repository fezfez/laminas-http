<?php

declare(strict_types=1);

namespace LaminasTest\Http\Header;

use Laminas\Http\Header\ContentTransferEncoding;
use Laminas\Http\Header\Exception\InvalidArgumentException;
use Laminas\Http\Header\HeaderInterface;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class ContentTransferEncodingTest extends TestCase
{
    public function testContentTransferEncodingFromStringCreatesValidContentTransferEncodingHeader(): void
    {
        $contentTransferEncodingHeader = ContentTransferEncoding::fromString('Content-Transfer-Encoding: xxx');
        $this->assertInstanceOf(HeaderInterface::class, $contentTransferEncodingHeader);
        $this->assertInstanceOf(ContentTransferEncoding::class, $contentTransferEncodingHeader);
    }

    public function testContentTransferEncodingGetFieldNameReturnsHeaderName(): void
    {
        $contentTransferEncodingHeader = new ContentTransferEncoding();
        $this->assertEquals('Content-Transfer-Encoding', $contentTransferEncodingHeader->getFieldName());
    }

    public function testContentTransferEncodingGetFieldValueReturnsProperValue(): void
    {
        $this->markTestIncomplete('ContentTransferEncoding needs to be completed');

        $contentTransferEncodingHeader = new ContentTransferEncoding();
        $this->assertEquals('xxx', $contentTransferEncodingHeader->getFieldValue());
    }

    public function testContentTransferEncodingToStringReturnsHeaderFormattedString(): void
    {
        $this->markTestIncomplete('ContentTransferEncoding needs to be completed');

        $contentTransferEncodingHeader = new ContentTransferEncoding();

        // @todo set some values, then test output
        $this->assertEmpty('Content-Transfer-Encoding: xxx', $contentTransferEncodingHeader->toString());
    }

    /** Implementation specific tests here */
    /**
     * @see http://en.wikipedia.org/wiki/HTTP_response_splitting
     */
    #[Group('ZF2015-04')]
    public function testPreventsCRLFAttackViaFromString(): void
    {
        $this->expectException(InvalidArgumentException::class);
        ContentTransferEncoding::fromString("Content-Transfer-Encoding: xxx\r\n\r\nevilContent");
    }

    /**
     * @see http://en.wikipedia.org/wiki/HTTP_response_splitting
     */
    #[Group('ZF2015-04')]
    public function testPreventsCRLFAttackViaConstructor(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ContentTransferEncoding("xxx\r\n\r\nevilContent");
    }
}

<?php

namespace LaminasTest\Http\Header;

use Laminas\Http\Header\Exception\InvalidArgumentException;
use Laminas\Http\Header\HeaderInterface;
use Laminas\Http\Header\TransferEncoding;
use PHPUnit\Framework\TestCase;

class TransferEncodingTest extends TestCase
{
    public function testTransferEncodingFromStringCreatesValidTransferEncodingHeader()
    {
        $transferEncodingHeader = TransferEncoding::fromString('Transfer-Encoding: xxx');
        $this->assertInstanceOf(HeaderInterface::class, $transferEncodingHeader);
        $this->assertInstanceOf(TransferEncoding::class, $transferEncodingHeader);
    }

    public function testTransferEncodingGetFieldNameReturnsHeaderName()
    {
        $transferEncodingHeader = new TransferEncoding();
        $this->assertEquals('Transfer-Encoding', $transferEncodingHeader->getFieldName());
    }

    public function testTransferEncodingGetFieldValueReturnsProperValue()
    {
        $this->markTestIncomplete('TransferEncoding needs to be completed');

        $transferEncodingHeader = new TransferEncoding();
        $this->assertEquals('xxx', $transferEncodingHeader->getFieldValue());
    }

    public function testTransferEncodingToStringReturnsHeaderFormattedString()
    {
        $this->markTestIncomplete('TransferEncoding needs to be completed');

        $transferEncodingHeader = new TransferEncoding();

        // @todo set some values, then test output
        $this->assertEmpty('Transfer-Encoding: xxx', $transferEncodingHeader->toString());
    }

    /** Implementation specific tests here */

    /**
     * @see http://en.wikipedia.org/wiki/HTTP_response_splitting
     *
     * @group ZF2015-04
     */
    public function testPreventsCRLFAttackViaFromString()
    {
        $this->expectException(InvalidArgumentException::class);
        TransferEncoding::fromString("Transfer-Encoding: xxx\r\n\r\nevilContent");
    }

    /**
     * @see http://en.wikipedia.org/wiki/HTTP_response_splitting
     *
     * @group ZF2015-04
     */
    public function testPreventsCRLFAttackViaConstructor()
    {
        $this->expectException(InvalidArgumentException::class);
        new TransferEncoding("xxx\r\n\r\nevilContent");
    }
}

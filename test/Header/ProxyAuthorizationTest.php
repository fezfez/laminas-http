<?php

declare(strict_types=1);

namespace LaminasTest\Http\Header;

use Laminas\Http\Header\Exception\InvalidArgumentException;
use Laminas\Http\Header\HeaderInterface;
use Laminas\Http\Header\ProxyAuthorization;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class ProxyAuthorizationTest extends TestCase
{
    public function testProxyAuthorizationFromStringCreatesValidProxyAuthorizationHeader(): void
    {
        $proxyAuthorizationHeader = ProxyAuthorization::fromString('Proxy-Authorization: xxx');
        $this->assertInstanceOf(HeaderInterface::class, $proxyAuthorizationHeader);
        $this->assertInstanceOf(ProxyAuthorization::class, $proxyAuthorizationHeader);
    }

    public function testProxyAuthorizationGetFieldNameReturnsHeaderName(): void
    {
        $proxyAuthorizationHeader = new ProxyAuthorization();
        $this->assertEquals('Proxy-Authorization', $proxyAuthorizationHeader->getFieldName());
    }

    public function testProxyAuthorizationGetFieldValueReturnsProperValue(): void
    {
        $this->markTestIncomplete('ProxyAuthorization needs to be completed');

        $proxyAuthorizationHeader = new ProxyAuthorization();
        $this->assertEquals('xxx', $proxyAuthorizationHeader->getFieldValue());
    }

    public function testProxyAuthorizationToStringReturnsHeaderFormattedString(): void
    {
        $this->markTestIncomplete('ProxyAuthorization needs to be completed');

        $proxyAuthorizationHeader = new ProxyAuthorization();

        // @todo set some values, then test output
        $this->assertEmpty('Proxy-Authorization: xxx', $proxyAuthorizationHeader->toString());
    }

    /** Implementation specific tests here */
    /**
     * @see http://en.wikipedia.org/wiki/HTTP_response_splitting
     */
    #[Group('ZF2015-04')]
    public function testPreventsCRLFAttackViaFromString(): void
    {
        $this->expectException(InvalidArgumentException::class);
        ProxyAuthorization::fromString("Proxy-Authorization: xxx\r\n\r\nevilContent");
    }

    /**
     * @see http://en.wikipedia.org/wiki/HTTP_response_splitting
     */
    #[Group('ZF2015-04')]
    public function testPreventsCRLFAttackViaConstructor(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ProxyAuthorization("xxx\r\n\r\nevilContent");
    }
}

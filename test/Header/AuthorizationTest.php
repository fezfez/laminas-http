<?php

declare(strict_types=1);

namespace LaminasTest\Http\Header;

use Laminas\Http\Header\Authorization;
use Laminas\Http\Header\Exception\InvalidArgumentException;
use Laminas\Http\Header\HeaderInterface;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class AuthorizationTest extends TestCase
{
    public function testAuthorizationFromStringCreatesValidAuthorizationHeader(): void
    {
        $authorizationHeader = Authorization::fromString('Authorization: xxx');
        $this->assertInstanceOf(HeaderInterface::class, $authorizationHeader);
        $this->assertInstanceOf(Authorization::class, $authorizationHeader);
    }

    public function testAuthorizationGetFieldNameReturnsHeaderName(): void
    {
        $authorizationHeader = new Authorization();
        $this->assertEquals('Authorization', $authorizationHeader->getFieldName());
    }

    public function testAuthorizationGetFieldValueReturnsProperValue(): void
    {
        $authorizationHeader = new Authorization('xxx');
        $this->assertEquals('xxx', $authorizationHeader->getFieldValue());
    }

    public function testAuthorizationToStringReturnsHeaderFormattedString(): void
    {
        $authorizationHeader = new Authorization('xxx');
        $this->assertEquals('Authorization: xxx', $authorizationHeader->toString());

        $authorizationHeader = Authorization::fromString('Authorization: xxx2');
        $this->assertEquals('Authorization: xxx2', $authorizationHeader->toString());
    }

    /** Implementation specific tests here */
    /**
     * @see http://en.wikipedia.org/wiki/HTTP_response_splitting
     */
    #[Group('ZF2015-04')]
    public function testPreventsCRLFAttackViaFromString(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $header = Authorization::fromString("Authorization: xxx\r\n\r\nevilContent");
    }

    /**
     * @see http://en.wikipedia.org/wiki/HTTP_response_splitting
     */
    #[Group('ZF2015-04')]
    public function testPreventsCRLFAttackViaConstructor(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Authorization("xxx\r\n\r\nevilContent");
    }
}

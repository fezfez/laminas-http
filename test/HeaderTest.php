<?php

declare(strict_types=1);

namespace LaminasTest\Http;

use Laminas\Http\Exception\InvalidArgumentException;
use Laminas\Http\Header;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function strtolower;
use function strtoupper;

class HeaderTest extends TestCase
{
    /** @psalm-return iterable<string, array{0: class-string, 1: string}> */
    public static function header(): iterable
    {
        // phpcs:disable Generic.Files.LineLength.TooLong
        yield Header\AcceptRanges::class            => [Header\AcceptRanges::class, 'Accept-Ranges'];
        yield Header\AuthenticationInfo::class      => [Header\AuthenticationInfo::class, 'Authentication-Info'];
        yield Header\Authorization::class           => [Header\Authorization::class, 'Authorization'];
        yield Header\ContentDisposition::class      => [Header\ContentDisposition::class, 'Content-Disposition'];
        yield Header\ContentEncoding::class         => [Header\ContentEncoding::class, 'Content-Encoding'];
        yield Header\ContentLanguage::class         => [Header\ContentLanguage::class, 'Content-Language'];
        yield Header\ContentLength::class           => [Header\ContentLength::class, 'Content-Length'];
        yield Header\ContentMD5::class              => [Header\ContentMD5::class, 'Content-MD5'];
        yield Header\ContentRange::class            => [Header\ContentRange::class, 'Content-Range'];
        yield Header\ContentTransferEncoding::class => [Header\ContentTransferEncoding::class, 'Content-Transfer-Encoding'];
        yield Header\ContentType::class             => [Header\ContentType::class, 'Content-Type'];
        yield Header\Etag::class                    => [Header\Etag::class, 'Etag'];
        yield Header\Expect::class                  => [Header\Expect::class, 'Expect'];
        yield Header\From::class                    => [Header\From::class, 'From'];
        yield Header\Host::class                    => [Header\Host::class, 'Host'];
        yield Header\IfMatch::class                 => [Header\IfMatch::class, 'If-Match'];
        yield Header\IfNoneMatch::class             => [Header\IfNoneMatch::class, 'If-None-Match'];
        yield Header\IfRange::class                 => [Header\IfRange::class, 'If-Range'];
        yield Header\KeepAlive::class               => [Header\KeepAlive::class, 'Keep-Alive'];
        yield Header\MaxForwards::class             => [Header\MaxForwards::class, 'Max-Forwards'];
        yield Header\Origin::class                  => [Header\Origin::class, 'Origin'];
        yield Header\Pragma::class                  => [Header\Pragma::class, 'Pragma'];
        yield Header\ProxyAuthenticate::class       => [Header\ProxyAuthenticate::class, 'Proxy-Authenticate'];
        yield Header\ProxyAuthorization::class      => [Header\ProxyAuthorization::class, 'Proxy-Authorization'];
        yield Header\Range::class                   => [Header\Range::class, 'Range'];
        yield Header\Refresh::class                 => [Header\Refresh::class, 'Refresh'];
        yield Header\Server::class                  => [Header\Server::class, 'Server'];
        yield Header\TE::class                      => [Header\TE::class, 'TE'];
        yield Header\Trailer::class                 => [Header\Trailer::class, 'Trailer'];
        yield Header\TransferEncoding::class        => [Header\TransferEncoding::class, 'Transfer-Encoding'];
        yield Header\Upgrade::class                 => [Header\Upgrade::class, 'Upgrade'];
        yield Header\UserAgent::class               => [Header\UserAgent::class, 'User-Agent'];
        yield Header\Vary::class                    => [Header\Vary::class, 'Vary'];
        yield Header\Via::class                     => [Header\Via::class, 'Via'];
        yield Header\Warning::class                 => [Header\Warning::class, 'Warning'];
        yield Header\WWWAuthenticate::class         => [Header\WWWAuthenticate::class, 'WWW-Authenticate'];
        // phpcs:enable
    }

    /**
     * phpcs:ignore SlevomatCodingStandard.Namespaces.UnusedUses.MismatchingCaseSensitivity
     *
     */
    #[DataProvider('header')]
    public function testThrowsExceptionIfInvalidHeaderLine(string $class, string $name): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid header line for ' . $name . ' string');
        $class::fromString($name . '-Foo: bar');
    }

    /**
     * phpcs:ignore SlevomatCodingStandard.Namespaces.UnusedUses.MismatchingCaseSensitivity
     *
     */
    #[DataProvider('header')]
    public function testCaseInsensitiveHeaderName(string $class, string $name): void
    {
        $header1 = $class::fromString(strtoupper($name) . ': foo');
        self::assertSame('foo', $header1->getFieldValue());

        $header2 = $class::fromString(strtolower($name) . ': bar');
        self::assertSame('bar', $header2->getFieldValue());
    }

    /**
     * phpcs:ignore SlevomatCodingStandard.Namespaces.UnusedUses.MismatchingCaseSensitivity
     *
     */
    #[DataProvider('header')]
    public function testDefaultValues(string $class, string $name): void
    {
        $header = new $class();

        self::assertSame('', $header->getFieldValue());
        self::assertSame($name, $header->getFieldName());
        self::assertSame($name . ': ', $header->toString());
    }

    /**
     * phpcs:ignore SlevomatCodingStandard.Namespaces.UnusedUses.MismatchingCaseSensitivity
     *
     */
    #[DataProvider('header')]
    public function testSetValueViaConstructor(string $class, string $name): void
    {
        $header = new $class('foo-bar');

        self::assertSame('foo-bar', $header->getFieldValue());
        self::assertSame($name . ': foo-bar', $header->toString());
    }

    /**
     * phpcs:ignore SlevomatCodingStandard.Namespaces.UnusedUses.MismatchingCaseSensitivity
     *
     * @param string $name
     *
     * Note: in theory this is invalid, as we would expect value to be string|null.
     * Null is default value but it is converted to string.
     */
    #[DataProvider('header')]
    public function testSetIntValueViaConstructor(string $class, string $name): void
    {
        $header = new $class(100);

        self::assertSame('100', $header->getFieldValue());
        self::assertSame($name . ': 100', $header->toString());
    }

    /**
     * phpcs:ignore SlevomatCodingStandard.Namespaces.UnusedUses.MismatchingCaseSensitivity
     *
     */
    #[DataProvider('header')]
    public function testSetZeroStringValueViaConstructor(string $class, string $name): void
    {
        $header = new $class('0');

        self::assertSame('0', $header->getFieldValue());
        self::assertSame($name . ': 0', $header->toString());
    }

    /**
     * phpcs:ignore SlevomatCodingStandard.Namespaces.UnusedUses.MismatchingCaseSensitivity
     *
     */
    #[DataProvider('header')]
    public function testFromStringWithNumber(string $class, string $name): void
    {
        $header = $class::fromString($name . ': 100');

        self::assertSame('100', $header->getFieldValue());
        self::assertSame($name . ': 100', $header->toString());
    }
}

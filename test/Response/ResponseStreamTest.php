<?php

declare(strict_types=1);

namespace LaminasTest\Http\Response;

use Laminas\Http\Response\Stream;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

use function fgets;
use function file_exists;
use function fopen;
use function fread;
use function fwrite;
use function md5;
use function rewind;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;

use const DIRECTORY_SEPARATOR;

#[CoversClass(Stream::class)]
class ResponseStreamTest extends TestCase
{
    /** @var null|string */
    private $tempFile;

    public function setUp(): void
    {
        $this->tempFile = null;
    }

    public function tearDown(): void
    {
        if (null !== $this->tempFile && file_exists($this->tempFile)) {
            unlink($this->tempFile);
        }
    }

    public function testResponseFactoryFromStringCreatesValidResponse(): void
    {
        $string = 'HTTP/1.0 200 OK' . "\r\n\r\n" . 'Foo Bar' . "\r\n";
        $stream = fopen('php://temp', 'rb+');
        fwrite($stream, 'Bar Foo');
        rewind($stream);

        $response = Stream::fromStream($string, $stream);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Foo Bar\r\nBar Foo", $response->getBody());
    }

    #[Group('6027')]
    public function testResponseFactoryFromEmptyStringCreatesValidResponse(): void
    {
        $stream = fopen('php://temp', 'rb+');
        fwrite($stream, 'HTTP/1.0 200 OK' . "\r\n\r\n" . 'Foo Bar' . "\r\n" . 'Bar Foo');
        rewind($stream);

        $response = Stream::fromStream('', $stream);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Foo Bar\r\nBar Foo", $response->getBody());
    }

    public function testGzipResponse(): void
    {
        $stream = fopen(__DIR__ . '/../_files/response_gzip', 'rb');

        $headers = '';
        while (false !== ($newLine = fgets($stream))) {
            $headers .= $newLine;
            if ($headers === "\n" || $headers === "\r\n") {
                break;
            }
        }

        $headers .= fread($stream, 100); // Should accept also part of body as text

        $res = Stream::fromStream($headers, $stream);

        $this->assertEquals('gzip', $res->getHeaders()->get('Content-encoding')->getFieldValue());
        $this->assertEquals('0b13cb193de9450aa70a6403e2c9902f', md5($res->getBody()));
        $this->assertEquals('f24dd075ba2ebfb3bf21270e3fdc5303', md5($res->getContent()));
    }

    public function test300isRedirect(): void
    {
        $values   = $this->readResponse('response_302');
        $response = Stream::fromStream($values['data'], $values['stream']);

        $this->assertEquals(302, $response->getStatusCode(), 'Response code is expected to be 302, but it\'s not.');
        $this->assertFalse($response->isClientError(), 'Response is an error, but isClientError() returned true');
        $this->assertFalse($response->isForbidden(), 'Response is an error, but isForbidden() returned true');
        $this->assertFalse($response->isInformational(), 'Response is an error, but isInformational() returned true');
        $this->assertFalse($response->isNotFound(), 'Response is an error, but isNotFound() returned true');
        $this->assertFalse($response->isOk(), 'Response is an error, but isOk() returned true');
        $this->assertFalse($response->isServerError(), 'Response is an error, but isServerError() returned true');
        $this->assertTrue($response->isRedirect(), 'Response is an error, but isRedirect() returned false');
        $this->assertFalse($response->isSuccess(), 'Response is an error, but isSuccess() returned true');
    }

    /**
     * @see https://cve.mitre.org/cgi-bin/cvename.cgi?name=CVE-2021-3007
     */
    public function testDestructionDoesNothingIfStreamIsNotAResourceAndStreamNameIsNotAString(): void
    {
        $this->tempFile = tempnam(sys_get_temp_dir(), 'lhrs');
        $streamObject   = new class ($this->tempFile) {
            /** @var string */
            private $tempFile;

            public function __construct(string $tempFile)
            {
                $this->tempFile = $tempFile;
            }

            public function __toString(): string
            {
                return $this->tempFile;
            }
        };

        $response = new Stream();
        $response->setCleanup(true);
        $response->setStreamName($streamObject);

        unset($response);

        $this->assertFileExists($this->tempFile);
    }

    /**
     * Helper function: read test response from file
     */
    protected function readResponse(string $response): array
    {
        $stream = fopen(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR
            . $response, 'rb');

        $data = '';
        while (false !== ($newLine = fgets($stream))) {
            $data .= $newLine;
            if ($newLine === "\n" || $newLine === "\r\n") {
                break;
            }
        }

        $data .= fread($stream, 100); // Should accept also part of body as text

        $return           = [];
        $return['stream'] = $stream;
        $return['data']   = $data;

        return $return;
    }
}

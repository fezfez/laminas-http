<?php

namespace LaminasTest\Http\Client;

use ArrayObject;
use Laminas\Http\Client\Adapter;
use Laminas\Http\Client\Adapter\Exception\InvalidArgumentException;
use Laminas\Http\Client\Adapter\Exception\RuntimeException;
use Laminas\Http\Client\Adapter\Socket;
use Laminas\Uri\Uri;
use stdClass;

use function fopen;
use function get_resource_type;
use function md5;
use function microtime;
use function stream_context_create;
use function stream_context_get_options;

/**
 * This Testsuite includes all Laminas_Http_Client that require a working web
 * server to perform. It was designed to be extendable, so that several
 * test suites could be run against several servers, with different client
 * adapters and configurations.
 *
 * Note that $this->baseuri must point to a directory on a web server
 * containing all the files under the files directory. You should symlink
 * or copy these files and set 'baseuri' properly.
 *
 * You can also set the proper constant in your test configuration file to
 * point to the right place.
 *
 * @group      Laminas_Http
 * @group      Laminas_Http_Client
 */
class SocketTest extends CommonHttpTests
{
    /**
     * Configuration array
     *
     * @var array
     */
    protected $config = [
        'adapter' => Socket::class,
    ];

    /**
     * Off-line common adapter tests
     */

    /**
     * Test that we can set a valid configuration array with some options
     *
     * @group ZHC001
     */
    public function testConfigSetAsArray()
    {
        $config = [
            'timeout'    => 500,
            'someoption' => 'hasvalue',
        ];

        $this->adapter->setOptions($config);

        $hasConfig = $this->adapter->getConfig();
        foreach ($config as $k => $v) {
            $this->assertEquals($v, $hasConfig[$k]);
        }
    }

    public function testDefaultConfig()
    {
        $config = $this->adapter->getConfig();
        $this->assertEquals(true, $config['sslverifypeer']);
        $this->assertEquals(false, $config['sslallowselfsigned']);
        $this->assertEquals(true, $config['sslverifypeername']);
    }

    public function testConnectingViaSslEnforcesDefaultSslOptionsOnContext()
    {
        $config = ['timeout' => 30];
        $this->adapter->setOptions($config);
        try {
            $this->adapter->connect('localhost', 443, true);
        } catch (RuntimeException $e) {
            // Test is designed to allow connect failure because we're interested
            // only in the stream context state created within that method.
        }
        $context = $this->adapter->getStreamContext();
        $options = stream_context_get_options($context);
        $this->assertTrue($options['ssl']['verify_peer']);
        $this->assertFalse($options['ssl']['allow_self_signed']);
        $this->assertTrue($options['ssl']['verify_peer_name']);
    }

    public function testConnectingViaSslWithCustomSslOptionsOnContext()
    {
        $config = [
            'timeout'            => 30,
            'sslverifypeer'      => false,
            'sslallowselfsigned' => true,
            'sslverifypeername'  => false,
        ];
        $this->adapter->setOptions($config);
        try {
            $this->adapter->connect('localhost', 443, true);
        } catch (RuntimeException $e) {
            // Test is designed to allow connect failure because we're interested
            // only in the stream context state created within that method.
        }
        $context = $this->adapter->getStreamContext();
        $options = stream_context_get_options($context);
        $this->assertFalse($options['ssl']['verify_peer']);
        $this->assertTrue($options['ssl']['allow_self_signed']);
        $this->assertFalse($options['ssl']['verify_peer_name']);
    }

    /**
     * Test Certificate File Option
     * The configuration is set to a legitimate certificate bundle file,
     * to exclude errors from being thrown from an invalid cafile context being set.
     */
    public function testConnectingViaSslUsesCertificateFileContext()
    {
        $config = [
            'timeout'   => 30,
            'sslcafile' => __DIR__ . '/_files/ca-bundle.crt',
        ];
        $this->adapter->setOptions($config);
        try {
            $this->adapter->connect('localhost', 443, true);
        } catch (RuntimeException $e) {
            // Test is designed to allow connect failure because we're interested
            // only in the stream context state created within that method.
        }
        $context = $this->adapter->getStreamContext();
        $options = stream_context_get_options($context);
        $this->assertEquals($config['sslcafile'], $options['ssl']['cafile']);
    }

    /**
     * Test that a Traversable object can be used to set configuration
     *
     * @link https://framework.zend.com/issues/browse/ZEND-5577
     */
    public function testConfigSetAsTraversable()
    {
        $config = new ArrayObject([
            'timeout' => 400,
            'nested'  => [
                'item' => 'value',
            ],
        ]);

        $this->adapter->setOptions($config);

        $hasConfig = $this->adapter->getConfig();
        $this->assertEquals($config['timeout'], $hasConfig['timeout']);
        $this->assertEquals($config['nested']['item'], $hasConfig['nested']['item']);
    }

    /**
     * Check that an exception is thrown when trying to set invalid config
     *
     * @dataProvider invalidConfigProvider
     * @param mixed $config
     */
    public function testSetConfigInvalidConfig($config)
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Array or Laminas\Config object expected');

        $this->adapter->setOptions($config);
    }

    /** @psalm-return array<string, array{0: int|string}> */
    public function provideValidTimeoutConfig(): array
    {
        return [
            'integer' => [10],
            'numeric' => ['10'],
        ];
    }

    /**
     * @dataProvider provideValidTimeoutConfig
     * @param int|string $timeout
     */
    public function testPassValidTimeout($timeout)
    {
        $adapter = new Adapter\Socket();
        $adapter->setOptions(['timeout' => $timeout]);

        $adapter->connect('getlaminas.org');
    }

    public function testThrowInvalidArgumentExceptionOnNonIntegerAndNonNumericStringTimeout()
    {
        $adapter = new Adapter\Socket();
        $adapter->setOptions(['timeout' => 'timeout']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('integer or numeric string expected, got string');

        $adapter->connect('getlaminas.org');
    }

    // Stream context related tests

    // phpcs:ignore Squiz.Commenting.FunctionComment.WrongStyle
    public function testGetNewStreamContext()
    {
        $adapterClass = $this->config['adapter'];
        $adapter      = new $adapterClass();
        $context      = $adapter->getStreamContext();

        $this->assertEquals('stream-context', get_resource_type($context));
    }

    public function testSetNewStreamContextResource()
    {
        $adapterClass = $this->config['adapter'];
        $adapter      = new $adapterClass();
        $context      = stream_context_create();

        $adapter->setStreamContext($context);

        $this->assertEquals($context, $adapter->getStreamContext());
    }

    public function testSetNewStreamContextOptions()
    {
        $adapterClass = $this->config['adapter'];
        $adapter      = new $adapterClass();
        $options      = [
            'socket' => [
                'bindto' => '1.2.3.4:0',
            ],
            'ssl'    => [
                'capath'            => null,
                'verify_peer'       => true,
                'allow_self_signed' => false,
                'verify_peer_name'  => true,
            ],
        ];

        $adapter->setStreamContext($options);

        $this->assertEquals($options, stream_context_get_options($adapter->getStreamContext()));
    }

    /**
     * Test that setting invalid options / context causes an exception
     *
     * @dataProvider invalidContextProvider
     * @param mixed $invalid
     */
    public function testSetInvalidContextOptions($invalid)
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expecting either a stream context resource or array');

        $adapterClass = $this->config['adapter'];
        $adapter      = new $adapterClass();
        $adapter->setStreamContext($invalid);
    }

    public function testSetHttpsStreamContextParam()
    {
        if ($this->client->getUri()->getScheme() !== 'https') {
            $this->markTestSkipped();
        }

        $adapterClass = $this->config['adapter'];
        $adapter      = new $adapterClass();
        $adapter->setStreamContext([
            'ssl' => [
                'capture_peer_cert'  => true,
                'capture_peer_chain' => true,
            ],
        ]);

        $this->client->setAdapter($adapter);
        $this->client->setUri($this->baseuri . '/testSimpleRequests.php');
        $this->client->request();

        $opts = stream_context_get_options($adapter->getStreamContext());
        $this->assertTrue(isset($opts['ssl']['peer_certificate']));
    }

    /**
     * Test that we get the right exception after a socket timeout
     *
     * @link https://getlaminas.org/issues/browse/Laminas-7309
     */
    public function testExceptionOnReadTimeout()
    {
        // Set 1 second timeout
        $this->client->setOptions(['timeout' => 1]);

        $start = microtime(true);

        try {
            $this->client->send();
            $this->fail('Expected a timeout Laminas\Http\Client\Adapter\Exception\TimeoutException');
        } catch (Adapter\Exception\TimeoutException $e) {
            $this->assertEquals(Adapter\Exception\TimeoutException::READ_TIMEOUT, $e->getCode());
        }

        $time = microtime(true) - $start;

        // We should be very close to 1 second
        $this->assertLessThan(2, $time);
    }

    /**
     * Test that a chunked response with multibyte characters is properly read
     *
     * This can fail in various PHP environments - for example, when mbstring
     * overloads substr() and strlen(), and mbstring's internal encoding is
     * not a single-byte encoding.
     *
     * @link https://getlaminas.org/issues/browse/Laminas-6218
     */
    public function testMultibyteChunkedResponseLaminas6218()
    {
        $md5 = '7667818873302f9995be3798d503d8d3';

        $response = $this->client->send();
        $this->assertEquals($md5, md5($response->getBody()));
    }

    /**
     * Verifies that writing on a socket is considered valid even if 0 bytes
     * were written.
     *
     * @runInSeparateProcess
     */
    public function testAllowsZeroWrittenBytes()
    {
        $this->adapter->connect('localhost');
        require_once __DIR__ . '/_files/fwrite.php';
        $this->adapter->write('GET', new Uri('tcp://localhost:80/'), '1.1', [], 'test body');
    }

    /**
     * Verifies that the headers are being set as given without changing any
     * character case.
     */
    public function testCaseInsensitiveHeaders()
    {
        $this->adapter->connect('localhost');
        $requestString = $this->adapter->write(
            'GET',
            new Uri('tcp://localhost:80/'),
            '1.1',
            ['x-test-header' => 'someTestHeader'],
            'someTestBody'
        );

        $this->assertStringContainsString('x-test-header', $requestString);
    }

    /**
     * Data Providers
     */

    /**
     * Provide invalid context resources / options
     *
     * @return array
     */
    public static function invalidContextProvider()
    {
        return [
            [new stdClass()],
            [fopen('data://text/plain,', 'r')],
            [false],
            [null],
        ];
    }
}

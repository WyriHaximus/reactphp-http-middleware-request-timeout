<?php declare(strict_types=1);

namespace WyriHaximus\React\Tests\Http\Middleware;

use ApiClients\Tools\TestUtilities\TestCase;
use React\EventLoop\Factory;
use React\Promise\Deferred;
use function React\Promise\resolve;
use React\Promise\Timer\TimeoutException;
use RingCentral\Psr7\Response;
use RingCentral\Psr7\ServerRequest;
use WyriHaximus\React\Http\Middleware\RequestTimeoutMiddleware;

/**
 * @internal
 */
final class RequestTimeoutMiddlewareTest extends TestCase
{
    public function testTimeout(): void
    {
        self::expectException(TimeoutException::class);
        self::expectExceptionMessage('Timed out after 0.1 seconds');

        $loop = Factory::create();
        $timeout = 0.1;
        $middleware = new RequestTimeoutMiddleware($loop, $timeout);

        $this->await(
            $middleware(
                new ServerRequest('GET', 'https://example.com/'),
                function () {
                    return (new Deferred())->promise();
                }
            ),
            $loop,
            1
        );
    }

    public function testDefaultTimeout(): void
    {
        self::expectException(TimeoutException::class);
        self::expectExceptionMessage('Timed out after 30 seconds');

        $loop = Factory::create();
        $middleware = new RequestTimeoutMiddleware($loop);

        $this->await(
            $middleware(
                new ServerRequest('GET', 'https://example.com/'),
                function () {
                    return (new Deferred())->promise();
                }
            ),
            $loop,
            31
        );
    }

    public function testRequestIsFasterThenTheTimeout(): void
    {
        $loop = Factory::create();
        $middleware = new RequestTimeoutMiddleware($loop);

        $this->await(
            $middleware(
                new ServerRequest('GET', 'https://example.com/'),
                function () {
                    return resolve(new Response());
                }
            ),
            $loop,
            1
        );

        self::assertTrue(true); // No timeout exception
    }
}

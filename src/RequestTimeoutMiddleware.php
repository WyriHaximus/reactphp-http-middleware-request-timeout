<?php declare(strict_types=1);

namespace WyriHaximus\React\Http\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use React\EventLoop\LoopInterface;
use function React\Promise\resolve;
use function React\Promise\Timer\timeout;

final class RequestTimeoutMiddleware
{
    private const DEFAULT_TIMEOUT = 30.0;

    /** @var LoopInterface */
    private $loop;

    /** @var float */
    private $timeout = self::DEFAULT_TIMEOUT;

    public function __construct(LoopInterface $loop, float $timeout = self::DEFAULT_TIMEOUT)
    {
        $this->loop = $loop;
        $this->timeout = $timeout;
    }

    public function __invoke(ServerRequestInterface $request, callable $next)
    {
        return timeout(
            resolve($next($request)),
            $this->timeout,
            $this->loop
        );
    }
}

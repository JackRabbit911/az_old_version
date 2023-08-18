<?php declare(strict_types=1);

namespace Tests\Az\Route;

// use Az\Route\RouteCollection;
use Az\Route\Route;

use HttpSoft\Message\ServerRequest;
// use HttpSoft\Message\Uri;
use Tests\Mock\Uri;

use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertTrue;

final class RouteTest extends TestCase
{
    private ServerRequest $request;

    public function setUp(): void
    {
        $this->request = new ServerRequest();
        // $this->uri = $this->createMock(Uri::class);
        // $this->uri->method('doSomething')
        //      ->willReturn('foo');
    }

    public function matchProvider(): array
    {
        return [
            ['/foo/{a}/{b?}/{c?}', '/foo/a/b/c', ['a'=>'a', 'b'=>'b', 'c'=>'c']],
            ['/foo/{a}/{b?}/{c?}', '/foo/a/b', ['a'=>'a', 'b'=>'b']],
            ['/foo/{a}/{b?}/{c?}', '/foo/a', ['a'=>'a']],
            ['/foo/{a}/{b/c?}', '/foo/a/b/c', ['a'=>'a', 'b'=>'b', 'c'=>'c']],
            ['/foo/{a}/{b/c?}', '/foo/a/b', ['a'=>'a', 'b'=>'b']],
            ['/foo/{a}/{b/c?}', '/foo/a', ['a'=>'a']],
            ['/foo/{a}/{b?}-{c?}', '/foo/a/b-c', ['a'=>'a', 'b'=>'b', 'c'=>'c']],
            ['/foo/{a}/{b?}&{c?}', '/foo/a/b&c', ['a'=>'a', 'b'=>'b', 'c'=>'c']],
            ['/foo/{a}/{b?}+{c?}', '/foo/a/b+c', ['a'=>'a', 'b'=>'b', 'c'=>'c']],
            ['/foo/{a}/{b-c?}', '/foo/a/b-c', ['a'=>'a', 'b'=>'b', 'c'=>'c']],
            ['/foo/{a}/{b&c?}', '/foo/a/b&c', ['a'=>'a', 'b'=>'b', 'c'=>'c']],
            ['/foo/{a}/{b+c?}', '/foo/a/b+c', ['a'=>'a', 'b'=>'b', 'c'=>'c']],
            ['/foo/{a}/{b!c?}', '/foo/a/b_c', ['a'=>'a', 'b'=>'b_c']],
            ['/foo+{a}:{b?}', '/foo+a:b', ['a'=>'a', 'b'=>'b']],
            ['/foo/{a}/{b?}+{c?}', '/foo/a/b/', ['a'=>'a', 'b'=>'b']],
            ['/foo/{a}/{b?}/{c?}', '/foo/a/b/c/', ['a'=>'a', 'b'=>'b', 'c'=>'c']],
            ['/foo/{a}/{b/c?}', '/foo/a/b/', ['a'=>'a', 'b'=>'b']],
            ['/{foo?}', '/', []],
            ['/', '/', []],
            ['', '/', []],
        ];
    }

    /**
     * @dataProvider matchProvider
     * @param string $pattern
     * @param string $uri
     * @param array $params
     */
    public function testMatch($pattern, $uri, $params)
    {
        $request = $this->request->withUri(new Uri($uri));
        $route = new Route('test', $pattern, 'handler');
        $match = $route->match($request);

        assertTrue($match);
        assertSame($params, $route->getParameters());
    }

    public function notMatchProvider(): array
    {
        return [          
            ['/foo/{a}/{b?}/{c?}', '/foo/a/b!'],
            ['/foo/{a}/{b?}/{c?}', '/foo-a'],
            ['/foo/{a}/{b/c?}', '/foo/a/b/c/d'],
            ['/foo/{a}/{b/c?}', '/foo'],
            ['/foo/{a}/{b?}-{c?}', '/foo/a/b/c'],
            ['/foo/{a}/{b?}&{c?}', '/foo/a/b&'],           
            ['/foo/{a}/{b?}', '/bar/a/b'],
        ];
    }

    /**
     * @dataProvider notMatchProvider
     * @param string $pattern
     * @param string $uri
     */
    public function testNotMatch($pattern, $uri)
    {
        $request = $this->request->withUri(new Uri($uri));
        $route = new Route('test', $pattern, 'handler');
        $match = $route->match($request);

        assertFalse($match);
        assertSame([], $route->getParameters());
    }

    public function parametersProvider()
    {
        return [
            ['/foo/{a}/{b?}', '/foo/a', ['a'=>'a', 'b'=>'d']],
            ['/foo/{a}/{b?}', '/foo/a/b', ['a'=>'a', 'b'=>'b']],
        ];
    }

    /**
     * @dataProvider parametersProvider
     * @param string $pattern
     * @param string $uri
     * @param array $params
     */
    public function testParameters($pattern, $uri, $params)
    {
        $request = $this->request->withUri(new Uri($uri));
        $route = new Route('test', $pattern, 'handler');
        $route->defaults(['b'=>'d']);
        $match = $route->match($request);

        // var_dump($params, $route->getParameters()); exit;

        assertEquals($params, $route->getParameters());
    }

    public function testGetters()
    {
        $route = new Route('test', '/', 'handler');

        assertEquals('test', $route->getName());
        assertEquals('handler', $route->getHandler());
    }

    public function tokensProvider()
    {
        return [
            ['/123', '\d+', true],
            ['/123a', '\d+', false],
            ['/foo', 'foo|bar', true],
            ['/fou', 'foo|bar', false],
        ];
    }

     /**
     * @dataProvider tokensProvider
     * @param string $uri
     * @param string $regex
     * @param bool $result
     */
    public function testTokens($uri, $regex, $result)
    {
        $request = $this->request->withUri(new Uri($uri));
        $route = new Route('test', '/{id}', 'handler');
        $route->tokens(['id' => $regex]);
        $match = $route->match($request);

        assertEquals($result, $match);
    }

    public function testHost()
    {
        $uri = (new Uri('/'))->withHost('example.com');
        $request = $this->request->withUri($uri);
        $route = new Route('test', '/', 'handler');
        $route->host('example.com');
        $match = $route->match($request);

        assertTrue($match);
    }

    public function testHostFailed()
    {
        $uri = (new Uri('/'))->withHost('example.com');
        $request = $this->request->withUri($uri);
        $route = new Route('test', '/', 'handler');
        $route->host('localhost');
        $match = $route->match($request);

        assertFalse($match);
    }

    public function testMethod()
    {
        $route = new Route('test', '/', 'handler', ['GET', 'post']);
        $match = $route->match($this->request->withMethod('post'));

        assertTrue($match);
    }

    public function testMethodFailed()
    {
        $route = new Route('test', '/', 'handler', ['GET', 'post']);
        $match = $route->match($this->request->withMethod('delete'));

        assertFalse($match);
    }

    public function pathProvider()
    {
        return [
            ['/foo/{a}/{b?}', ['a' => 'bar'], '/foo/bar'],
            ['/foo/{a}/{b?}', ['a' => 'bar', 'b' => 'baz'], '/foo/bar/baz'],
            ['/foo/{a}/{b/c?}', ['a' => 'bar'], '/foo/bar'],
            ['/foo/{a}/{b/c?}', ['a' => 'bar', 'b' => 'baz'], '/foo/bar/baz'],
            ['/foo/{a}/{b/c?}', ['a' => 'bar', 'b' => 'baz', 'c' => 'qqq'], '/foo/bar/baz/qqq'],
            ['/foo/{a}/{b-c?}', ['a' => 'bar', 'b' => 'baz', 'c' => 'qqq'], '/foo/bar/baz-qqq'],
        ];
    }

    /**
     * @dataProvider pathProvider
     * @param string $pattern
     * @param array $params
     * @param string $path
     */
    public function testPath($pattern, $params, $path)
    {
        $route = new Route('test', $pattern, 'handler');

        assertEquals($path, $route->path($params));
    }

    public function pathInvalidArgsProvider()
    {
        return [
            ['/foo/{a}/{b?}', ['b' => 'bar']],
            ['/foo/{a}/{b?}', ['c' => 'bar']],
            ['/foo/{a}/{b}/{c?}', ['a' => 'bar', 'c' => 'baz']],
            ['/foo/{a}/{b/c?}', ['c' => 'bar']],
        ];
    }

    /**
     * @dataProvider pathInvalidArgsProvider
     * @param string $pattern
     * @param array $params
     */
    public function testPathInvalidArgs($pattern, $params)
    {
        $route = new Route('test', $pattern, 'handler');

        $this->expectException(\InvalidArgumentException::class);

        $route->path($params);

        // var_dump($route->path($params), $params); exit;
    }
}

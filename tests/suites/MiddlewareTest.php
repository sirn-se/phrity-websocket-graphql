<?php

declare(strict_types=1);

namespace Phrity\WebSocket\GraphQL\Test;

use Closure;
use GraphQL\Server\ServerConfig;
use GraphQL\Type\{
    Schema,
    SchemaConfig,
};
use GraphQL\Type\Definition\{
    ObjectType,
    Type,
};
use PHPUnit\Framework\TestCase;
use Phrity\WebSocket\GraphQL\Middleware;
use Phrity\Net\Mock\{
    Mock,
    StreamFactory,
};
use WebSocket\{
    Client,
    Server,
};

class MiddlewareTest extends TestCase
{
    public function setUp(): void
    {
        error_reporting(-1);
    }

    public function testContentTypeJson(): void
    {
        Mock::setCallback(function (int $counter, string $method, array $params, Closure $default, object $instance) {
            switch ($counter) {
                case 8:
                    return $instance;
                case 17:
                    return "GET / HTTP/1.1\r\n";
                case 18:
                    return "Host: 127.0.0.1\r\n";
                case 19:
                    return "User-Agent: websocket-client-php\r\n";
                case 20:
                    return "Connection: Upgrade\r\n";
                case 21:
                    return "Upgrade: websocket\r\n";
                case 22:
                    return "Sec-WebSocket-Key: cktLWXhUdDQ2OXF0ZCFqOQ==\r\n";
                case 23:
                    return "Sec-WebSocket-Version: 13\r\n";
                case 24:
                    return "Content-Type: application/json\r\n";
                case 25:
                    return "\r\n";
                case 27:
                    return base64_decode('gZU=');
                case 28:
                    return base64_decode('89kk+g==');
                case 29:
                    return base64_decode('iPtVj5arXdjJ+1/am7xIlpz5WdiO');
                case 30:
                    // Write server response
                    $this->assertEquals('gRp7ImRhdGEiOnsiaGVsbG8iOiJ3b3JsZCJ9fQ==', base64_encode($params[0]));
                    return $default($params);
                default:
                    return $default($params);
            }
        });

        $gqlConfig = $this->getGqlConfig();

        $server = new Server(8081, streamFactory: new StreamFactory());
        $server->addMiddleware(new Middleware($gqlConfig));
        $server->onText(function ($server, $connection, $message) {
            $this->assertEquals('{"query":"{ hello }"}', $message->getContent());
            $server->stop();
        });
        $server->start();
        $server->disconnect();
    }

    public function testContentTypeGraphQL(): void
    {
        Mock::setCallback(function (int $counter, string $method, array $params, Closure $default, object $instance) {
            switch ($counter) {
                case 8:
                    return $instance;
                case 17:
                    return "GET / HTTP/1.1\r\n";
                case 18:
                    return "Host: 127.0.0.1\r\n";
                case 19:
                    return "User-Agent: websocket-client-php\r\n";
                case 20:
                    return "Connection: Upgrade\r\n";
                case 21:
                    return "Upgrade: websocket\r\n";
                case 22:
                    return "Sec-WebSocket-Key: cktLWXhUdDQ2OXF0ZCFqOQ==\r\n";
                case 23:
                    return "Sec-WebSocket-Version: 13\r\n";
                case 24:
                    return "Content-Type: application/graphql\r\n";
                case 25:
                    return "\r\n";
                case 27:
                    return base64_decode('gYk=');
                case 28:
                    return base64_decode('9EsFJQ==');
                case 29:
                    return base64_decode('j2ttQJgnagWJ');
                case 30:
                    // Write server response
                    $this->assertEquals('gRp7ImRhdGEiOnsiaGVsbG8iOiJ3b3JsZCJ9fQ==', base64_encode($params[0]));
                    return $default($params);
                default:
                    return $default($params);
            }
        });

        $gqlConfig = $this->getGqlConfig();

        $server = new Server(8081, streamFactory: new StreamFactory());
        $server->addMiddleware(new Middleware($gqlConfig));
        $server->onText(function ($server, $connection, $message) {
            $this->assertEquals('{ hello }', $message->getContent());
            $server->stop();
        });
        $server->start();
        $server->disconnect();
    }

    public function testInvalidContentType(): void
    {
        Mock::setCallback(function (int $counter, string $method, array $params, Closure $default, object $instance) {
            switch ($counter) {
                case 8:
                    return $instance;
                case 17:
                    return "GET / HTTP/1.1\r\n";
                case 18:
                    return "Host: 127.0.0.1\r\n";
                case 19:
                    return "User-Agent: websocket-client-php\r\n";
                case 20:
                    return "Connection: Upgrade\r\n";
                case 21:
                    return "Upgrade: websocket\r\n";
                case 22:
                    return "Sec-WebSocket-Key: cktLWXhUdDQ2OXF0ZCFqOQ==\r\n";
                case 23:
                    return "Sec-WebSocket-Version: 13\r\n";
                case 24:
                    return "Content-Type: application/invalid\r\n";
                case 25:
                    return "\r\n";
                case 26:
                    // Write server response
                    $expect = "HTTP/1.1 415 Unsupported Media Type\r\n"
                        . "Upgrade: websocket\r\n"
                        . "Connection: Upgrade\r\n"
                        . "Sec-WebSocket-Accept: YmysboNHNoWzWVeQpduY7xELjgU=\r\n"
                        . "\r\n";
                    $this->assertEquals($expect, $params[0]);
                    return $default($params);
                default:
                    return $default($params);
            }
        });

        $gqlConfig = $this->getGqlConfig();

        $server = new Server(8081, streamFactory: new StreamFactory());
        $server->addMiddleware(new Middleware($gqlConfig));
        $server->onError(function ($server, $connection, $message) {
            $server->stop();
        });
        $server->start();
        $server->disconnect();
    }

    public function testClientIgnore(): void
    {
        $lastWsKey = null;
        Mock::setCallback(function (int $counter, string $method, array $params, Closure $default) use (&$lastWsKey) {
            switch ($counter) {
                case 16:
                    preg_match('/Sec-WebSocket-Key: ([\S]*)\r\n/', $params[0], $m);
                    $lastWsKey = $m[1] ?? null;
                    return $default($params);
                case 17:
                    return "HTTP/1.1 101 Switching Protocols\r\n";
                case 18:
                    return "Upgrade: websocket\r\n";
                case 19:
                    return "Connection: Upgrade\r\n";
                case 20:
                    $wsKeyRes = base64_encode(pack('H*', sha1($lastWsKey . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
                    return "Sec-WebSocket-Accept: {$wsKeyRes}\r\n";
                case 21:
                    return "\r\n";
                case 23:
                    return base64_decode('ios=');
                case 24:
                    return base64_decode('AQEBAQ==');
                case 25:
                    return base64_decode('UmRzd2RzIXFob2Y=');
                default:
                    return $default($params);
            }
        });

        $gqlConfig = $this->getGqlConfig();

        $client = new Client('ws://localhost:8000/my/mock/path', streamFactory: new StreamFactory());
        $client->addMiddleware(new Middleware($gqlConfig));
        $client->addHeader('Content-Type', 'application/json');

        $client->connect();
        $message = $client->receive();
        $this->assertEquals('Server ping', $message->getContent());
        $client->disconnect();
    }

    private function getGqlConfig(): ServerConfig
    {
        $query = new ObjectType([
            'name' => 'Query',
            'fields' => [
                'hello' => [
                    'type' => Type::string(),
                    'args' => [],
                    'resolve' => function ($objectValue, $args, $uow) {
                        return 'world';
                    },
                ],
            ],
        ]);
        $config = SchemaConfig::create()->setQuery($query);
        return ServerConfig::create([
            'schema' => new Schema($config),
        ]);
    }
}

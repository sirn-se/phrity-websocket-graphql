<?php

declare(strict_types=1);

namespace Phrity\WebSocket\GraphQL\Test;

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
    SocketStream,
    StreamCollection,
    StreamFactory,
};
use Phrity\Net\Mock\Stack\{
    ExpectContextTrait,
    ExpectSocketClientTrait,
    ExpectSocketServerTrait,
    ExpectSocketStreamTrait,
    ExpectStreamCollectionTrait,
    ExpectStreamFactoryTrait
};
use WebSocket\{
    Client,
    Connection,
    Server,
};

class MiddlewareTest extends TestCase
{
    use ExpectContextTrait;
    use ExpectSocketClientTrait;
    use ExpectSocketServerTrait;
    use ExpectSocketStreamTrait;
    use ExpectStreamCollectionTrait;
    use ExpectStreamFactoryTrait;

    private string $lastWsKey = '';

    public function setUp(): void
    {
        error_reporting(-1);
        $this->setUpStack();
    }

    public function tearDown(): void
    {
        $this->tearDownStack();
    }

    public function testContentTypeJson(): void
    {
        $gqlConfig = $this->getGqlConfig();

        $server = new Server(8081);
        $this->expectStreamFactory();
        $server->setStreamFactory(new StreamFactory());
        $server->addMiddleware(new Middleware($gqlConfig));

        $server->onText(function ($server, $connection, $message) {
            $this->assertEquals('{"query":"{ hello }"}', $message->getContent());
            $server->stop();
        });

        $this->expectServerSetUp();
        $this->expectSelect('@server');

        $this->expectSocketServerAccept();
        $this->expectSocketStream();
        $this->expectSocketStreamGetMetadata();
        $this->expectContext();
        $this->expectSocketStreamGetRemoteName()->setReturn(function () {
            return 'fake-connection-1';
        });
        $this->expectStreamCollectionAttach();
        $this->expectSocketStreamGetLocalName()->setReturn(function () {
            return 'fake-connection-1';
        });
        $this->expectSocketStreamGetRemoteName();
        $this->expectSocketStreamSetTimeout();

        $this->expectSocketStreamReadLine()->setReturn(function (array $params) {
            return "GET / HTTP/1.1\r\n";
        });
        $this->expectSocketStreamReadLine()->setReturn(function (array $params) {
            return "Host: 127.0.0.1\r\n";
        });
        $this->expectSocketStreamReadLine()->setReturn(function (array $params) {
            return "User-Agent: websocket-client-php\r\n";
        });
        $this->expectSocketStreamReadLine()->setReturn(function (array $params) {
            return "Connection: Upgrade\r\n";
        });
        $this->expectSocketStreamReadLine()->setReturn(function (array $params) {
            return "Upgrade: websocket\r\n";
        });
        $this->expectSocketStreamReadLine()->setReturn(function (array $params) {
            return "Sec-WebSocket-Key: cktLWXhUdDQ2OXF0ZCFqOQ==\r\n";
        });
        $this->expectSocketStreamReadLine()->setReturn(function (array $params) {
            return "Sec-WebSocket-Version: 13\r\n";
        });
        $this->expectSocketStreamReadLine()->setReturn(function (array $params) {
            return "Content-Type: application/json\r\n";
        });
        $this->expectSocketStreamReadLine()->setReturn(function (array $params) {
            return "\r\n";
        });
        $this->expectSocketStreamWrite();

        $this->expectSocketStreamIsConnected();
        $this->expectSelect('fake-connection-1');

        $this->expectSocketStreamRead()->setReturn(function () {
            return base64_decode('gZU=');
        });
        $this->expectSocketStreamRead()->setReturn(function () {
            return base64_decode('89kk+g==');
        });
        $this->expectSocketStreamRead()->setReturn(function () {
            return base64_decode('iPtVj5arXdjJ+1/am7xIlpz5WdiO');
        });

        $this->expectSocketStreamWrite();

        $server->start();

        $this->expectSocketStreamClose();
        $this->expectSocketServerClose();
        $server->disconnect();

        unset($server);
    }

    public function testContentTypeGraphQL(): void
    {
        $gqlConfig = $this->getGqlConfig();

        $server = new Server(8081);
        $this->expectStreamFactory();
        $server->setStreamFactory(new StreamFactory());
        $server->addMiddleware(new Middleware($gqlConfig));

        $server->onText(function ($server, $connection, $message) {
            $this->assertEquals('{ hello }', $message->getContent());
            $server->stop();
        });

        $this->expectServerSetUp();
        $this->expectSelect('@server');

        $this->expectSocketServerAccept();
        $this->expectSocketStream();
        $this->expectSocketStreamGetMetadata();
        $this->expectContext();
        $this->expectSocketStreamGetRemoteName()->setReturn(function () {
            return 'fake-connection-1';
        });
        $this->expectStreamCollectionAttach();
        $this->expectSocketStreamGetLocalName()->setReturn(function () {
            return 'fake-connection-1';
        });
        $this->expectSocketStreamGetRemoteName();
        $this->expectSocketStreamSetTimeout();

        $this->expectSocketStreamReadLine()->setReturn(function (array $params) {
            return "GET / HTTP/1.1\r\n";
        });
        $this->expectSocketStreamReadLine()->setReturn(function (array $params) {
            return "Host: 127.0.0.1\r\n";
        });
        $this->expectSocketStreamReadLine()->setReturn(function (array $params) {
            return "User-Agent: websocket-client-php\r\n";
        });
        $this->expectSocketStreamReadLine()->setReturn(function (array $params) {
            return "Connection: Upgrade\r\n";
        });
        $this->expectSocketStreamReadLine()->setReturn(function (array $params) {
            return "Upgrade: websocket\r\n";
        });
        $this->expectSocketStreamReadLine()->setReturn(function (array $params) {
            return "Sec-WebSocket-Key: cktLWXhUdDQ2OXF0ZCFqOQ==\r\n";
        });
        $this->expectSocketStreamReadLine()->setReturn(function (array $params) {
            return "Sec-WebSocket-Version: 13\r\n";
        });
        $this->expectSocketStreamReadLine()->setReturn(function (array $params) {
            return "Content-Type: application/graphql\r\n";
        });
        $this->expectSocketStreamReadLine()->setReturn(function (array $params) {
            return "\r\n";
        });
        $this->expectSocketStreamWrite();

        $this->expectSocketStreamIsConnected();
        $this->expectSelect('fake-connection-1');

        $this->expectSocketStreamRead()->setReturn(function () {
            return base64_decode('gYk=');
        });
        $this->expectSocketStreamRead()->setReturn(function () {
            return base64_decode('9EsFJQ==');
        });
        $this->expectSocketStreamRead()->setReturn(function () {
            return base64_decode('j2ttQJgnagWJ');
        });

        $this->expectSocketStreamWrite();

        $server->start();

        $this->expectSocketStreamClose();
        $this->expectSocketServerClose();
        $server->disconnect();

        unset($server);
    }

    public function testInvalidContentType(): void
    {
        $gqlConfig = $this->getGqlConfig();

        $server = new Server(8081);
        $this->expectStreamFactory();
        $server->setStreamFactory(new StreamFactory());
        $server->addMiddleware(new Middleware($gqlConfig));

        $server->onError(function ($server, $connection, $message) {
            $server->stop();
        });

        $this->expectServerSetUp();
        $this->expectSelect('@server');

        $this->expectSocketServerAccept();
        $this->expectSocketStream();
        $this->expectSocketStreamGetMetadata();
        $this->expectContext();
        $this->expectSocketStreamGetRemoteName()->setReturn(function () {
            return 'fake-connection-1';
        });
        $this->expectStreamCollectionAttach();
        $this->expectSocketStreamGetLocalName()->setReturn(function () {
            return 'fake-connection-1';
        });
        $this->expectSocketStreamGetRemoteName();
        $this->expectSocketStreamSetTimeout();

        $this->expectSocketStreamReadLine()->setReturn(function (array $params) {
            return "GET / HTTP/1.1\r\n";
        });
        $this->expectSocketStreamReadLine()->setReturn(function (array $params) {
            return "Host: 127.0.0.1\r\n";
        });
        $this->expectSocketStreamReadLine()->setReturn(function (array $params) {
            return "User-Agent: websocket-client-php\r\n";
        });
        $this->expectSocketStreamReadLine()->setReturn(function (array $params) {
            return "Connection: Upgrade\r\n";
        });
        $this->expectSocketStreamReadLine()->setReturn(function (array $params) {
            return "Upgrade: websocket\r\n";
        });
        $this->expectSocketStreamReadLine()->setReturn(function (array $params) {
            return "Sec-WebSocket-Key: cktLWXhUdDQ2OXF0ZCFqOQ==\r\n";
        });
        $this->expectSocketStreamReadLine()->setReturn(function (array $params) {
            return "Sec-WebSocket-Version: 13\r\n";
        });
        $this->expectSocketStreamReadLine()->setReturn(function (array $params) {
            return "Content-Type: application/invalid\r\n";
        });
        $this->expectSocketStreamReadLine()->setReturn(function (array $params) {
            return "\r\n";
        });
        $this->expectSocketStreamWrite()->addAssert(function ($method, $params) {
            $expect = "HTTP/1.1 415 Unsupported Media Type\r\n"
                    . "Upgrade: websocket\r\n"
                    . "Connection: Upgrade\r\n"
                    . "Sec-WebSocket-Accept: YmysboNHNoWzWVeQpduY7xELjgU=\r\n"
                    . "\r\n";
            $this->assertEquals($expect, $params[0]);
        });
        $this->expectSocketStreamClose();
        $server->start();

        $this->expectSocketServerClose();
        $server->disconnect();
        unset($server);
    }

    public function testClientIgnore(): void
    {
        $gqlConfig = $this->getGqlConfig();

        $client = new Client('ws://localhost:8000/my/mock/path');
        $this->expectStreamFactory();
        $client->setStreamFactory(new StreamFactory());
        $client->addMiddleware(new Middleware($gqlConfig));
        $client->addHeader('Content-Type', 'application/json');

        $this->expectStreamFactoryCreateStreamCollection();
        $this->expectStreamCollection();
        $this->expectStreamFactoryCreateSocketClient();
        $this->expectSocketClient();
        $this->expectSocketClientSetPersistent();
        $this->expectSocketClientSetTimeout();

        $this->expectSocketClientConnect();
        $this->expectSocketStream();
        $this->expectSocketStreamGetMetadata();
        $this->expectContext();

        $this->expectSocketStreamGetRemoteName()->setReturn(function () {
            return 'fake-connection-1';
        });
        $this->expectStreamCollectionAttach();
        $this->expectSocketStreamGetLocalName()->setReturn(function () {
            return 'fake-connection-1';
        });
        $this->expectSocketStreamGetRemoteName()->setReturn(function () {
            return 'fake-connection-1';
        });
        $this->expectSocketStreamSetTimeout();
        $this->expectSocketStreamIsConnected();

        $this->expectSocketStreamWrite()->addAssert(
            function (string $method, array $params) {
                preg_match('/Sec-WebSocket-Key: ([\S]*)\r\n/', $params[0], $m);
                $this->lastWsKey = $m[1] ?? '';
            }
        );
        $this->expectSocketStreamReadLine()->setReturn(function (array $params) {
            return "HTTP/1.1 101 Switching Protocols\r\n";
        });
        $this->expectSocketStreamReadLine()->setReturn(function (array $params) {
            return "Upgrade: websocket\r\n";
        });
        $this->expectSocketStreamReadLine()->setReturn(function (array $params) {
            return "Connection: Upgrade\r\n";
        });
        $this->expectSocketStreamReadLine()->setReturn(function (array $params) {
            $wsKeyRes = base64_encode(pack('H*', sha1($this->lastWsKey . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
            return "Sec-WebSocket-Accept: {$wsKeyRes}\r\n\r\n";
        });
        $this->expectSocketStreamReadLine()->setReturn(function (array $params) {
            return "\r\n";
        });
        $client->connect();

        // Receiving pong for first ping
        $this->expectSocketStreamIsConnected();
        $this->expectSocketStreamRead()->setReturn(function () {
            return base64_decode('ios=');
        });
        $this->expectSocketStreamRead()->setReturn(function () {
            return base64_decode('AQEBAQ==');
        });
        $this->expectSocketStreamRead()->setReturn(function () {
            return base64_decode('UmRzd2RzIXFob2Y=');
        });
        $message = $client->receive();

        $this->expectSocketStreamIsConnected();
        $this->expectSocketStreamClose();
        $client->disconnect();
        unset($client);
    }


    private function expectServerSetUp(): void
    {
        $this->expectStreamFactoryCreateSocketServer();
        $this->expectSocketServer();
        $this->expectSocketServerGetTransports();
        $this->expectSocketServerGetMetadata();
        $this->expectStreamFactoryCreateStreamCollection();
        $this->expectStreamCollection();
        $this->expectStreamCollectionAttach();
    }

    private function expectSelect(string $find): void
    {
        $this->expectStreamCollectionWaitRead()->setReturn(function ($params, $default, $collection) use ($find) {
            $selected = new StreamCollection();
            foreach ($collection as $key => $stream) {
                if ($key == $find) {
                    $selected->attach($stream, $key);
                }
            }
            return $selected;
        });
        $this->expectStreamCollection();
        $this->expectStreamCollectionAttach();
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

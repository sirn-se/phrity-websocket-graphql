<?php

use Phrity\WebSocket\GraphQL\EchoLog;
use Phrity\WebSocket\GraphQL\Middleware;
use WebSocket\Connection;
use WebSocket\Server;
use WebSocket\Message\Text;
use WebSocket\Exception\ExceptionInterface;
use WebSocket\Middleware\{
    CloseHandler,
    PingResponder,
};
use GraphQL\Server\{
    Helper,
    RequestError,
    ServerConfig,
    OperationParams,
};
use GraphQL\Error\{
    ClientAware,
    DebugFlag,
};
use GraphQL\Type\{
    Schema,
    SchemaConfig,
};
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

require __DIR__ . '/../vendor/autoload.php';

echo "# GraphQL server! [phrity/websocket-graphql]\n";

// Server options specified or default
/**
 * @var array{
 *     port: int<1, 32768>,
 *     ssl: bool,
 *     timeout: int<0, max>|float,
 *     framesize: int<1, max>,
 *     connections: int<0, max>|null,
 *     deflate: bool,
 * } $options
 */
$options = array_merge([
    'port'  => 80,
], getopt('', ['port:', 'ssl', 'timeout:', 'framesize:', 'connections:']));

// Initiate server.
try {
    $query = new ObjectType([
        'name' => 'Query',
        'fields' => [
            'hello' => [
                'type' => Type::string(),
                'args' => [],
                'resolve' => function ($objectValue, $args, $uow) {
                    return "world";
                },
            ],
        ],
    ]);
    $config = SchemaConfig::create()->setQuery($query);
    $config = ServerConfig::create(['schema' => new Schema($config)]);

    $server = new Server($options['port'], !empty($options['ssl']));
    $server
        ->addMiddleware(new CloseHandler())
        ->addMiddleware(new PingResponder())
        ->addMiddleware(new Middleware($config))
        ;

    if (isset($options['timeout'])) {
        $server->setTimeout($options['timeout']);
        echo "# Set timeout: {$options['timeout']}\n";
    }
    if (isset($options['framesize'])) {
        $server->setFrameSize($options['framesize']);
        echo "# Set frame size: {$options['framesize']}\n";
    }
    if (isset($options['connections'])) {
        $server->setMaxConnections($options['connections']);
        echo "# Set max connections: {$options['connections']}\n";
    }

    echo "# Listening on port {$server->getPort()}\n";
    $server->onError(function (Server $server, Connection|null $connection, ExceptionInterface $exception) {
        echo "> Error: {$exception->getMessage()}\n";
    });
    $server->onText(function (Server $server, Connection|null $connection, Text $message) {
        echo "> Query: {$message->getContent()}\n";
    });
    $server->start();
} catch (Throwable $e) {
    echo "# ERROR: {$e->getMessage()}\n\n{$e}\n";
}

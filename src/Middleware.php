<?php

namespace Phrity\WebSocket\GraphQL;

use GraphQL\Server\{
    Helper,
    ServerConfig,
    OperationParams,
};
use Psr\Http\Message\{
    MessageInterface,
    ResponseInterface,
    ServerRequestInterface,
};
use Psr\Log\LoggerAwareInterface;
use RuntimeException;
use Stringable;
use WebSocket\Connection;
use WebSocket\Message\{
    Message,
    Text,
};
use WebSocket\Middleware\{
    ProcessIncomingInterface,
    ProcessStack,
    ProcessHttpIncomingInterface,
    ProcessHttpOutgoingInterface,
    ProcessHttpStack,
};
use WebSocket\Trait\{
    LoggerAwareTrait,
    StringableTrait,
};

class Middleware implements
    LoggerAwareInterface,
    ProcessIncomingInterface,
    ProcessHttpIncomingInterface,
    ProcessHttpOutgoingInterface,
    Stringable
{
    use LoggerAwareTrait;
    use StringableTrait;

    private Helper $helper;
    private ServerConfig $serverConfig;

    public function __construct(ServerConfig $serverConfig)
    {
        $this->helper = new Helper();
        $this->serverConfig = $serverConfig;
    }

    public function processHttpIncoming(
        ProcessHttpStack $stack,
        Connection $connection
    ): MessageInterface {
        $message = $stack->handleHttpIncoming();
        if (!$message instanceof ServerRequestInterface) {
            return $message;
        }
        preg_match('#application/(graphql|json)#', $message->getHeaderLine('Content-Type'), $m);
        $connection->setMeta('graphql.requestType', $m[1] ?? null);
        return $message;
    }

    public function processHttpOutgoing(
        ProcessHttpStack $stack,
        Connection $connection,
        MessageInterface $message
    ): MessageInterface {
        if (!$message instanceof ResponseInterface) {
            return $stack->handleHttpOutgoing($message);
        }
        if (!$connection->getMeta('graphql.requestType')) {
            return $stack->handleHttpOutgoing($message->withStatus(415));
        }
        return $stack->handleHttpOutgoing(
            $message->withHeader('Content-Type', 'application/graphql-response+json;charset=utf-8')
        );
    }

    public function processIncoming(ProcessStack $stack, Connection $connection): Message
    {
        $message = $stack->handleIncoming();
        if (!$message instanceof Text) {
            return $message; // No action
        }
        /** @var "graphql"|"json" $requestType */
        $requestType = $connection->getMeta('graphql.requestType');
        $params = match ($requestType) {
            'graphql' => ['query' => $message->getContent()],
            'json' => json_decode($message->getContent(), associative: true, flags: JSON_THROW_ON_ERROR),
        };
        $operationParams = OperationParams::create($params);
        $response = $this->helper->executeOperation($this->serverConfig, $operationParams);
        $responseEncoded = json_encode($response, flags: JSON_THROW_ON_ERROR);
        $connection->text($responseEncoded);

        return $message;
    }
}

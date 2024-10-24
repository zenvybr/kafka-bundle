<?php

namespace Zenvy\Kafka\Producer;

use Interop\Queue\Context;
use Psr\Log\LoggerInterface;

abstract class KafkaMessageProducer
{
    public function __construct(
        protected Context $context,
        protected LoggerInterface $logger
    ) {
    }
    abstract protected function getTopicsToSend(): array;


    public function send(array $messageBody, array $properties = [], array $headers = []): int
    {
        $message = $this->context->createMessage(json_encode($messageBody), $properties, $headers);
        $topics = $this->getTopicsToSend();
        $this->logger->info("Sending kafka message...", ['message' => $message, 'topics' => $topics]);
        $producer = $this->context->createProducer();
        foreach ($topics as $topic) {
            $queue = $this->context->createTopic($topic);
            $producer->send($queue, $message);
            $this->logger->info("Message sent to topic {$topic} successfully.");
        }

        return true;
    }
}
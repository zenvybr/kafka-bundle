<?php

namespace Zenvy\Kafka\Producer;

use Exception;
use Interop\Queue\Context;
use Psr\Log\LoggerInterface;
use Interop\Queue\Message;

abstract class KafkaMessageProducerBase
{
    public function __construct(
        protected Context $context,
        protected LoggerInterface $logger
    ) {
    }
    abstract public function getTopicsToSend(): array;


    public function send(Message $message): int
    {
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
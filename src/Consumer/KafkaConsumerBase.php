<?php

namespace Zenvy\Kafka\Consumer;

use Exception;
use Interop\Queue\Context;
use Psr\Log\LoggerInterface;
use Interop\Queue\Message;

abstract class KafkaConsumerBase
{
    const REJECT_MESSAGE = 'enqueue.reject';
    const ATTEMPTS_PROPERTY = 'enqueue.attempts';
    const ERRORS_PROPERTY = 'enqueue.errors';
    public function __construct(
        protected LoggerInterface $logger
    ) {
    }

    abstract public function getDlqQueueName(): string;
    abstract public function getMaxProcessingAttempts(): int;


    public function sendToDLQ(Message $message, Context $context): string
    {
        $this->logger->warning("Sending message to DLQ...");
        $dlqQueue = $this->getDlqQueueName();
        $queue = $context->createTopic($dlqQueue);
        $producer = $context->createProducer();
        $producer->send($queue, $message);

        $this->logger->info("Message sent to DLQ successfully.");
        return self::REJECT_MESSAGE;
    }

    public function incrementAttempts(Message $message, string $errorMessage = ''): Message
    {
        $attempts = $this->getAttempts($message) + 1;
        $message->setProperty(self::ATTEMPTS_PROPERTY, $attempts);

        if (!empty($errorMessage)) {
            $errors = $message->getProperty(self::ERRORS_PROPERTY, []);
            $errors[] = $errorMessage;
            $message->setProperty(self::ERRORS_PROPERTY, $errors);
        }

        return $message;
    }

    public function getAttempts(Message $message): int
    {
        return $message->getProperty(self::ATTEMPTS_PROPERTY, 0);
    }

    public function isMaxAttemptsReached(Message $message): bool
    {
        $attemptsReached = $this->getAttempts($message) >= $this->getMaxProcessingAttempts();

        if ($attemptsReached) {
            $this->logger->warning("Maximum processing attempts reached.");
        }

        return $attemptsReached;
    }

    protected function parseMessage(Message $message): array
    {
        $data = json_decode($message->getBody(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON message body.');
        }
        return $data;
    }
}
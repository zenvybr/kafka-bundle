<?php

namespace Zenvy\Kafka\Producer;

interface KafkaMessageProducerInterface
{
    public function execute(array $message): int;
}
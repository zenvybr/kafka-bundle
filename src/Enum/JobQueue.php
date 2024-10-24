<?php

namespace Zenvy\Kafka\Enum;

enum JobQueue: string
{
    case FIRST_IN_FIRST_OUT = "fifo";
    case HIGH_PRIORITY = "high";
}

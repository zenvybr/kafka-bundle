<?php

namespace Zenvy\Kafka\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(name: 'create-consumer', aliases: ['add-consumer', 'consumer'])]
class CreateConsumerCommand extends Command
{
    private Filesystem $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        parent::__construct();
        $this->filesystem = $filesystem;
    }

    protected function configure()
    {
        $this
            ->setDescription('Generates a new consumer class')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the conmsumer class');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $className = $input->getArgument('name');
        $classPath ='src/Infrastructure/Kafka/Consumer' . $className . 'Consumer.php';

        $classContent = file_get_contents('src/Stub/ConsumerClass.stub');
        $classContent = str_replace('$className', $className, $classContent);

        try {
            $this->filesystem->dumpFile($classPath, $classContent);
            $output->writeln("Consumer class created successfully at $classPath. Don't forget to implement the process method, and replace getSubscribedQueues(), getDlqQueueName() and getMaxProcessingAttempts() values.");
        } catch (IOExceptionInterface $exception) {
            $output->writeln("Error creating consumer class: " . $exception->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}

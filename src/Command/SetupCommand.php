<?php

namespace Zenvy\Kafka\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(name: 'setup', aliases: ['configure'])]
class SetupCommand extends Command
{
    private Filesystem $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Sets up configuration files for the project.')
            ->setHelp('This command creates the enqueue.yaml file and updates the .env file with the variables.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $projectDir = getcwd();
        $configDir = $projectDir . '/config/packages';
        $envFile = $projectDir . '/.env';

        $messengerConfig = <<<YAML
        enqueue:
            default:
                transport:
                    dsn: '%env(ENQUEUE_DSN)%'
                    global:
                        group.id: '%env(KAFKA_CONSUMER_GROUP_ID)%'
                        metadata.broker.list: '%env(KAFKA_BROKER_URL)%'
                        enable.auto.commit: "false"
                    topic:
                        auto.offset.reset: 'beginning'
                client:
                    app_name: ''
        YAML;

        try {
            $this->filesystem->mkdir($configDir);
            $this->filesystem->dumpFile($configDir . '/enqueue.yaml', $messengerConfig);

            $envContent = file_get_contents($envFile);
            if (!str_contains($envContent, 'ENQUEUE_DSN')) {
                file_put_contents($envFile, PHP_EOL . 'ENQUEUE_DSN=rdkafka://kafka:9092', FILE_APPEND);
            }

            if (!str_contains($envContent, 'KAFKA_BROKER_URL')) {
                file_put_contents($envFile, PHP_EOL . 'KAFKA_BROKER_URL=kafka:9092', FILE_APPEND);
            }

            if (!str_contains($envContent, 'KAFKA_CONSUMER_GROUP_ID')) {
                file_put_contents($envFile, PHP_EOL . 'KAFKA_CONSUMER_GROUP_ID=my-consumer-group-name', FILE_APPEND);
            }

            $output->writeln('<info>Configuration files created and updated successfully. Next: Configure your ENQUEUE_DSN, KAFKA_BROKER_URL and KAFKA_CONSUMER_GROUP_ID values in .env file.</info>');

            $this->filesystem->mkdir($projectDir . '/src/Infrastructure/Kafka/Consumer');
            $this->filesystem->mkdir($projectDir . '/src/Infrastructure/Kafka/Command');
            $this->filesystem->mkdir($projectDir . '/src/Infrastructure/Kafka/Producer');

            return Command::SUCCESS;
        } catch (IOExceptionInterface $exception) {
            $output->writeln('<error>An error occurred while creating or updating files: ' . $exception->getMessage() . '</error>');
            return Command::FAILURE;
        }
    }
}

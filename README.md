# Zenvy Worker Bundle

Zenvy Worker Bundle is a PHP library designed to abstract the queuing of jobs for asynchronous processing by the worker service. The worker is responsible for ensuring that jobs are executed in the correct order and at the correct time, making it useful for scenarios that require sequential processing or asynchronous/multithreaded operations.

## How It Works

The library allows any application to enqueue a job in the Zenvy Worker servers, passing the necessary parameters for execution. The worker will then enqueue the job and ensure it is executed in the correct order by calling back a specified URL that was provided during job creation.

# Simplified Workers architecture
![Zenvy](https://github.com/user-attachments/assets/3a2e6a88-9516-47de-9bdb-5e7ad7dcd470)


## Use Case Example
Consider a Peer-to-Peer (P2P) transfer function within your software. When a client initiates a P2P transfer, instead of processing it immediately, the job is enqueued to ensure that more than one transfer for each user are not processed simultaneously. The worker guarantees that the jobs are executed in the order they were received, by calling back a specified URL in your service to actually execute the transfer.
## Installation

1. **Generate a GitHub Token**<br/>
Go to your GitHub account and create a new token with appropriate permissions.
   <br/><br/>
2. **Copy Auth File**<br/>
Copy using command above and replace `"your-github-token"` in copied file with the token you generated in step 1.
```bash
cp .composer/auth.json.example composer/auth.json
```

4. **Add Repository to Composer** <br/>
Add the following lines to your composer.json file:
```json
"repositories": [
   {
      "type": "vcs",
      "url": "https://github.com/zenvybr/worker-bundle"
   }
],

```
5. **Install the Library**
```bash
composer require "zenvy/worker-bundle"
```

## Usage
This library provides a series of commands to streamline the setup and configuration process with just a few steps.
<br/><br/>

1. **Auto setup Transporters**<br/>
```bash
php vendor/bin/zenvy-worker setup
``` 

2. **Create the postback route**<br/>
```bash
# This command will create a default route (/worker-postback)
php vendor/bin/zenvy-worker setup

# Or if you want to specify a custom route, you can run:
php vendor/bin/zenvy-worker route --route "/YOUR_ROUTE_NAME"
``` 

3. **Create a new job class**<br/>
```bash
# Pass the job name to command
php vendor/bin/zenvy-worker job MyTestJob
``` 

This will create a class like the following in the src/Jobs directory:
```php
readonly class MyTestJob implements ProccessableJobInterface
{
    public function __construct(
    private readonly JobsProcessorPostbackDto $dto, 
    private readonly ContainerInterface $container
    )
    {
    }

    public function execute(): void
    {   
    }
}
```

Now you can implement the `execute` method with the logic you want to run when the job is processed.<br/>
*Tip:* To get a service class from container, you can use `$this->container->get(YourService::class);` 

4. **Enqueue a job**<br/>
   - Inject the `JobDispatcherBuilder` class where you want to enqueue the job (it is already in the application's service container, just inject it):
   ```php
       public function __construct(private readonly JobDispatcherBuilder $jobDispatcherBuilder)
       {
       }
    ```
    - Enqueue the job:
   ```php
   $this->builder->setJobClass(MyTestJob::class) # Your created job class
              ->setUrl(getenv("MY_API_BASE_URL") . "/worker-postback") # Or your configured route
              ->setPayload(['test' => 'Success!']) # Your desired payload
              ->dispatch();
   ```

## Notes
- Ensure that the routes and job classes are correctly set up to avoid any issues during job execution.
- The current worker is designed to handle sequential job processing, making it ideal for scenarios requiring precise order of execution.
- It is also possible to enqueue jobs to call other applications. Just ensure that the target application has this library included and the postback route configured (steps 1 and 2 in the Usage section).

## Compatibility
This library is compatible with any Symfony application.

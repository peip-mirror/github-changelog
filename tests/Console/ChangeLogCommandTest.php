<?php

namespace Localheinz\GitHub\ChangeLog\Test\Console;

use Github\Client;
use Github\HttpClient;
use Localheinz\GitHub\ChangeLog\Console;
use Localheinz\GitHub\ChangeLog\Entity;
use Localheinz\GitHub\ChangeLog\Repository;
use Localheinz\GitHub\ChangeLog\Test\Util\FakerTrait;
use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;
use ReflectionObject;
use Symfony\Component\Console\Input;
use Symfony\Component\Console\Output;

class ChangeLogCommandTest extends PHPUnit_Framework_TestCase
{
    use FakerTrait;

    /**
     * @var Console\ChangeLogCommand
     */
    private $command;

    protected function setUp()
    {
        $this->command = new Console\ChangeLogCommand();

        $this->command->setClient($this->client());

        $pullRequestRepository = $this->pullRequestRepository();

        $pullRequestRepository
            ->expects($this->any())
            ->method('items')
            ->with($this->anything())
            ->willReturn([])
        ;

        $this->command->setPullRequestRepository($pullRequestRepository);
    }

    protected function tearDown()
    {
        unset($this->command);
    }

    public function testName()
    {
        $this->assertSame('localheinz:changelog', $this->command->getName());
    }

    public function testDescription()
    {
        $this->assertSame('Creates a changelog based on references', $this->command->getDescription());
    }

    /**
     * @dataProvider providerArgument
     *
     * @param string $name
     * @param bool $required
     * @param string $description
     */
    public function testArgument($name, $required, $description)
    {
        $this->assertTrue($this->command->getDefinition()->hasArgument($name));

        /* @var Input\InputArgument $argument */
        $argument = $this->command->getDefinition()->getArgument($name);

        $this->assertSame($name, $argument->getName());
        $this->assertSame($required, $argument->isRequired());
        $this->assertSame($description, $argument->getDescription());
    }

    /**
     * @return array
     */
    public function providerArgument()
    {
        return [
            [
                'vendor',
                true,
                'The name of the vendor, e.g., "localheinz"',
            ],
            [
                'package',
                true,
                'The name of the package, e.g. "github-changelog"',
            ],
            [
                'start-reference',
                true,
                'The start reference, e.g. "1.0.0"',
            ],
            [
                'end-reference',
                true,
                'The end reference, e.g. "1.1.0"',
            ],
        ];
    }

    /**
     * @dataProvider providerOption
     *
     * @param string $name
     * @param string $shortcut
     * @param bool $required
     * @param string $description
     * @param mixed $default
     */
    public function testOption($name, $shortcut, $required, $description, $default)
    {
        $this->assertTrue($this->command->getDefinition()->hasOption($name));

        /* @var Input\InputOption $option */
        $option = $this->command->getDefinition()->getOption($name);

        $this->assertSame($name, $option->getName());
        $this->assertSame($shortcut, $option->getShortcut());
        $this->assertSame($required, $option->isValueRequired());
        $this->assertSame($description, $option->getDescription());
        $this->assertSame($default, $option->getDefault());
    }

    /**
     * @return array
     */
    public function providerOption()
    {
        return [
            [
                'auth-token',
                'a',
                false,
                'The GitHub token',
                null,
            ],
            [
                'template',
                't',
                false,
                'The template to use for rendering a pull request',
                '- %title% (#%id%)',
            ],
        ];
    }

    public function testCanSetClient()
    {
        $client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->command->setClient($client);

        $reflectionObject = new ReflectionObject($this->command);

        $property = $reflectionObject->getProperty('client');
        $property->setAccessible(true);

        $this->assertSame($client, $property->getValue($this->command));
    }

    public function testExecuteLazilyCreatesClientWithCachedHttpClient()
    {
        $this->command = new Console\ChangeLogCommand();

        $this->command->run(
            $this->input(),
            $this->output()
        );

        $reflectionObject = new ReflectionObject($this->command);

        $property = $reflectionObject->getProperty('client');
        $property->setAccessible(true);

        $client = $property->getValue($this->command);

        $this->assertInstanceOf(Client::class, $client);

        /* @var Client $client */
        $this->assertInstanceOf(HttpClient\CachedHttpClient::class, $client->getHttpClient());
    }

    public function testExecuteAuthenticatesIfTokenOptionIsGiven()
    {
        $authToken = 'foo9000';

        $client = $this->client();

        $client
            ->expects($this->once())
            ->method('authenticate')
            ->with(
                $this->equalTo($authToken),
                $this->equalTo(Client::AUTH_HTTP_TOKEN)
            )
        ;

        $this->command->setClient($client);

        $this->command->run(
            $this->input(
                [],
                [
                    'auth-token' => $authToken,
                ]
            ),
            $this->output()
        );
    }

    public function testCanSetPullRequestRepository()
    {
        $pullRequestRepository = $this->pullRequestRepository();

        $this->command->setPullRequestRepository($pullRequestRepository);

        $reflectionObject = new ReflectionObject($this->command);

        $property = $reflectionObject->getProperty('pullRequestRepository');
        $property->setAccessible(true);

        $this->assertSame($pullRequestRepository, $property->getValue($this->command));
    }

    public function testExecuteLazilyCreatesPullRequestRepository()
    {
        $client = $this->client();

        $this->command->setClient($client);

        $this->command->run(
            $this->input(),
            $this->output()
        );

        $reflectionObject = new ReflectionObject($this->command);

        $property = $reflectionObject->getProperty('pullRequestRepository');
        $property->setAccessible(true);

        $pullRequestRepository = $property->getValue($this->command);

        $this->assertInstanceOf(Repository\PullRequest::class, $pullRequestRepository);
    }

    public function testExecuteDelegatesToPullRequestRepository()
    {
        $vendor = 'foo';
        $package = 'bar';
        $startReference = 'ad77125';
        $endReference = '7fc1c4f';

        $pullRequestRepository = $this->pullRequestRepository();

        $pullRequestRepository
            ->expects($this->once())
            ->method('items')
            ->with(
                $this->equalTo($vendor),
                $this->equalTo($package),
                $this->equalTo($startReference),
                $this->equalTo($endReference)
            )
            ->willReturn([])
        ;

        $this->command->setPullRequestRepository($pullRequestRepository);

        $this->command->run(
            $this->input([
                'vendor' => $vendor,
                'package' => $package,
                'start-reference' => $startReference,
                'end-reference' => $endReference,
            ]),
            $this->output()
        );
    }

    public function testExecuteRendersPullRequestsWithTheTemplate()
    {
        $pullRequests = $this->pullRequests(5);

        $template = '%title% :: %id%';

        $expectedMessages = [];

        array_walk($pullRequests, function (Entity\PullRequest $pullRequest) use (&$expectedMessages, $template) {
            $message = str_replace(
                [
                    '%title%',
                    '%id%',
                ],
                [
                    $pullRequest->title(),
                    $pullRequest->id(),
                ],
                $template
            );
            array_push($expectedMessages, $message);
        });

        $pullRequestRepository = $this->pullRequestRepository();

        $pullRequestRepository
            ->expects($this->any())
            ->method('items')
            ->willReturn($pullRequests)
        ;

        $this->command->setPullRequestRepository($pullRequestRepository);

        $arguments = [
            'vendor' => 'foo',
            'package' => 'bar',
            'start-reference' => 'ad77125',
            'end-reference' => '7fc1c4f',
        ];

        $options = [
            'template' => $template,
        ];

        $exitCode = $this->command->run(
            $this->input(
                $arguments,
                $options
            ),
            $this->output($expectedMessages)
        );

        $this->assertSame(0, $exitCode);
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|Client
     */
    private function client()
    {
        return $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|Repository\PullRequest
     */
    private function pullRequestRepository()
    {
        return $this->getMockBuilder(Repository\PullRequest::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
    }

    /**
     * @param array $arguments
     * @param array $options
     * @return Input\InputInterface
     */
    private function input(array $arguments = [], array $options = [])
    {
        $input = $this->getMockBuilder(Input\InputInterface::class)->getMock();

        $input
            ->expects($this->any())
            ->method('getArgument')
            ->willReturnCallback(function ($name) use ($arguments) {
                if (!array_key_exists($name, $arguments)) {
                    return null;
                }

                return $arguments[$name];
            })
        ;

        $input
            ->expects($this->any())
            ->method('getOption')
            ->willReturnCallback(function ($name) use ($options) {
                if (!array_key_exists($name, $options)) {
                    return null;
                }

                return $options[$name];
            })
        ;

        return $input;
    }

    /**
     * @param array $expectedMessages
     * @return PHPUnit_Framework_MockObject_MockObject|Output\OutputInterface
     */
    private function output(array $expectedMessages = [])
    {
        $output = $this->getMockBuilder(Output\OutputInterface::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $output
            ->expects($this->exactly(count($expectedMessages)))
            ->method('writeln')
            ->willReturnCallback(function ($message) use ($expectedMessages) {
                static $invocation = 0;

                $this->assertSame($message, $expectedMessages[$invocation]);

                $invocation++;
            })
        ;

        return $output;
    }

    /**
     * @return Entity\PullRequest
     */
    private function pullRequest()
    {
        return new Entity\PullRequest(
            $this->faker()->unique()->randomNumber,
            $this->faker()->unique()->sentence()
        );
    }

    /**
     * @param int $count
     * @return Entity\PullRequest[]
     */
    private function pullRequests($count)
    {
        $pullRequests = [];

        for ($i = 0; $i < $count; $i++) {
            array_push($pullRequests, $this->pullRequest());
        }

        return $pullRequests;
    }
}

<?php

namespace MageTitans\ProductStatus\Console\Command;

use MageTitans\ProductStatus\Model\Exception\ProductStatusAdapterException;
use MageTitans\ProductStatus\Model\ProductStatusAdapterInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProductStatusCommandTest extends \PHPUnit_Framework_TestCase
{

    private $command;
    private $mockInput;
    private $mockOutput;
    private $productStatusAdapter;

    protected function setUp()
    {
        $this->productStatusAdapter = $this->getMock(ProductStatusAdapterInterface::class);

        $this->command = new ProductStatusCommand($this->productStatusAdapter);
        $this->mockInput = $this->getMock(InputInterface::class);
        $this->mockOutput = $this->getMock(OutputInterface::class);
    }

    public function testItIsAConsoleCommand()
    {
        $this->assertInstanceOf(Command::class, $this->command);
    }

    public function testItHasTheCorrectName()
    {
        $this->assertSame('catalog:product:status', $this->command->getName());
    }

    public function testItHasADescription()
    {
        $this->assertNotEmpty($this->command->getDescription());
    }

    public function testItTakesARequiredSkuArgument()
    {
        $argument = $this->command->getDefinition()->getArgument('sku');
        $this->assertTrue($argument->isRequired());
        $this->assertNotEmpty($argument->getDescription());
    }

    public function testItDisplaysExceptionsAsErrors()
    {
        $this->productStatusAdapter->method('getProductStatusMatchingSku')
            ->willThrowException(
                new ProductStatusAdapterException('Dummy Exception')
            );
        $this->mockOutput->expects($this->once())
            ->method('writeln')
            ->with('<error>Dummy Exception</error>');
        $this->command->run($this->mockInput, $this->mockOutput);
    }

    public function testItDisplaysAMessageIfNoMatchingSkusAreFound()
    {
        $this->mockOutput->expects($this->once())
            ->method('writeln')
            ->with('<comment>No SKUs Matching "test" found</comment>');

        $this->mockInput->method('getArgument')->with('sku')
            ->willReturn('test');

        $this->command->run($this->mockInput, $this->mockOutput);
    }

    public function testItOutputsMatchingSkusWithTheirStatus()
    {
        $this->mockInput->method('getArgument')->with('sku')
            ->willReturn('test');

        $this->productStatusAdapter->method('getProductStatusMatchingSku')
            ->with('test')
            ->willReturn(
                [
                    'test1' => ProductStatusAdapterInterface::ENABLED,
                    'test2' => ProductStatusAdapterInterface::DISABLED
                ]
            );

        $this->mockOutput->expects($this->exactly(2))
            ->method('writeln')->withConsecutive(
                ['<info>test1: '.ProductStatusAdapterInterface::ENABLED.'</info>'],
                ['<info>test2: '.ProductStatusAdapterInterface::DISABLED.'</info>']
            );

        $this->command->run($this->mockInput, $this->mockOutput);
    }
}
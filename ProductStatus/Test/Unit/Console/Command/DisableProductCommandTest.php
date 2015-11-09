<?php

namespace MageTitans\ProductStatus\Console\Command;

use MageTitans\ProductStatus\Model\Exception\ProductStatusAdapterException;
use MageTitans\ProductStatus\Model\ProductStatusAdapterInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DisableProductCommandTest extends \PHPUnit_Framework_TestCase
{
    private $command;
    private $mockOutput;
    private $productStatusAdapter;
    private $mockInput;

    protected function setUp()
    {
        $this->productStatusAdapter = $this->getMock(ProductStatusAdapterInterface::class);
        $this->command = new DisableProductCommand($this->productStatusAdapter);
        $this->mockInput = $this->getMock(InputInterface::class);
        $this->mockOutput = $this->getMock(OutputInterface::class);
    }

    public function testItIsAConsoleCommand()
    {
        $this->assertInstanceOf(Command::class, $this->command);
    }

    public function testItHasTheRightName()
    {
        $this->assertSame('catalog:product:disable', $this->command->getName());
    }

    public function testItHasTheRightDescription()
    {
        $this->assertSame('Disable a product by SKU', $this->command->getDescription());
    }


    public function testItTakesARequiredSkuArgument()
    {
        $argument = $this->command->getDefinition()->getArgument('sku');
        $this->assertTrue($argument->isRequired());
        $this->assertNotEmpty($argument->getDescription());
    }

    public function testItDelegatesToTheProductStatusAdapter()
    {
        $this->productStatusAdapter->expects($this->once())->method('disableProductBySku');
        $this->command->run($this->mockInput, $this->mockOutput);
    }

    public function testItDisplaysExceptionsAsErrorMessages()
    {
        $this->productStatusAdapter->method('disableProductBySku')
            ->willThrowException(
                new ProductStatusAdapterException('Dummy Exception')
            );
        $this->mockOutput->expects($this->once())
            ->method('writeln')
            ->with('<error>Dummy Exception</error>');
        $this->command->run($this->mockInput, $this->mockOutput);
    }

    public function testItDisplaysAConfirmationMessage()
    {
        $this->mockInput->method('getArgument')->willReturn('test');
        $this->mockOutput->expects($this->once())->method('writeln')->with('<info>"test" was successfully disabled</info>');
        $this->command->run($this->mockInput, $this->mockOutput);
    }
}
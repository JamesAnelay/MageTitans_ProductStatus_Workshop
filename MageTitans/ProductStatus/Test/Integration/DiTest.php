<?php
namespace MageTitans\ProductStatus;

use Magento\Framework\Console\CommandList;
use Magento\TestFramework\Helper\Bootstrap;
use MageTitans\ProductStatus\Console\Command\DisableProductCommand;
use MageTitans\ProductStatus\Console\Command\EnableProductCommand;
use MageTitans\ProductStatus\Console\Command\ProductStatusCommand;

class DiTest extends \PHPUnit_Framework_TestCase
{

    private $commandList;
    private $commands;

    protected function setUp()
    {
        $this->commandList = Bootstrap::getObjectManager()->create(CommandList::class);
        $this->commands = $this->commandList->getCommands();
    }

    public function testTheShowProductStatusCommandIsRegistered()
    {
        $this->assertCommandIsRegisteredByName('catalogProductStatus', ProductStatusCommand::class);
    }

    public function testTheDisableProductCommandIsRegistered()
    {
        $this->assertCommandIsRegisteredByName('catalogProductDisable', DisableProductCommand::class);
    }

    public function testTheEnableProductCommandIsRegistered()
    {
        $this->assertCommandIsRegisteredByName('catalogProductEnable', EnableProductCommand::class);
    }

    private function assertCommandIsRegisteredByName($name, $expectedClass)
    {
        $this->assertArrayHasKey($name, $this->commands);
        $this->assertInstanceOf($expectedClass, $this->commands[$name]);
    }
}
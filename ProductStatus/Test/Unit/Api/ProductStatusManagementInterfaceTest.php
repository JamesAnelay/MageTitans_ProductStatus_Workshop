<?php

namespace MageTitans\ProductStatus\Api;

use MageTitans\ProductStatus\Model\Exception\ProductAlreadyDisabledException;
use MageTitans\ProductStatus\Model\ProductStatusAdapterInterface;
use MageTitans\ProductStatus\Model\ProductStatusManagement;

class ProductStatusManagementInterfaceTest extends \PHPUnit_Framework_TestCase
{
    private $productStatusManagementApi;
    private $productStatusAdapter;

    public function setUp()
    {
        $this->productStatusAdapter = $this->getMock(ProductStatusAdapterInterface::class);
        $this->productStatusManagementApi = new ProductStatusManagement($this->productStatusAdapter);
    }

    public function testItImplementsProductStatusManagementInterface()
    {
        $this->assertInstanceOf(ProductStatusmanagementInterface::class, $this->productStatusManagementApi);
    }

    public function testItReturnsTheProductStatus()
    {
        $this->productStatusAdapter->method('getStatusBySku')->willReturnMap([
            ['test1', ProductStatusAdapterInterface::ENABLED],
            ['test2', ProductStatusAdapterInterface::DISABLED],
        ]);
        $this->assertSame(ProductStatusAdapterInterface::ENABLED, $this->productStatusManagementApi->get('test1'));
        $this->assertSame(ProductStatusAdapterInterface::DISABLED, $this->productStatusManagementApi->get('test2'));
    }

    public function testItDelegatesToProductStatusAdapterGetStatusBySku()
    {
        $this->productStatusAdapter->expects($this->once())->method('getStatusBySku');
        $this->productStatusManagementApi->get('test');
    }

    public function testItEnablesAProducts(){
        $this->productStatusAdapter->expects($this->once())->method('enableProductBySku')->with('test');
        $this->productStatusManagementApi->set('test',ProductStatusAdapterInterface::ENABLED);
    }

    public function testItDisablesAProducts(){
        $this->productStatusAdapter->expects($this->once())->method('disableProductBySku')->with('test');
        $this->productStatusManagementApi->set('test',ProductStatusAdapterInterface::DISABLED);
    }

    public function testItThrowsAnExceptionifTheStatusIsInvalid(){
        $this->setExpectedException(\InvalidArgumentException::class, sprintf(
            'The product status you entered is not valid - it must match "%s" or "%s"',
            ProductStatusAdapterInterface::ENABLED,
            ProductStatusAdapterInterface::DISABLED
        ));
        $this->productStatusManagementApi->set('test','invalidstatus');
    }

    public function testItHidesProductAlreadyDisabledExceptions(){
        $this->productStatusAdapter->method('disableProductWithSku')
            ->willThrowException(new ProductAlreadyDisabledException('Dummy Exception'));
        $this->assertNotNull($this->productStatusManagementApi->set('test', 'disabled'));
    }

    public function testItHidesProductAlreadyEnabledExceptions(){
        $this->productStatusAdapter->method('enableProductWithSku')
            ->willThrowException(new ProductAlreadyDisabledException('Dummy Exception'));
        $this->assertNotNull($this->productStatusManagementApi->set('test', 'enabled'));
    }

    public function testItReturnsTheNewProductStatus(){
        $this->assertSame('enabled', $this->productStatusManagementApi->set('test', 'enabled'));
        $this->assertSame('disabled', $this->productStatusManagementApi->set('test', 'disabled'));
    }
}
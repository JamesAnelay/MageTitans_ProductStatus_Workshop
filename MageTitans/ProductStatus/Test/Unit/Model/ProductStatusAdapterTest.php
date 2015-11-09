<?php

namespace MageTitans\ProductStatus\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductSearchResultsInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\NoSuchEntityException;
use MageTitans\ProductStatus\Console\Command\ProductStatusCommand;
use MageTitans\ProductStatus\Model\Exception\InvalidSkuException;
use MageTitans\ProductStatus\Model\Exception\ProductAlreadyDisabledException;
use MageTitans\ProductStatus\Model\Exception\ProductStatusAdapterException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;
use Magento\Framework\App\State as AppState;

class ProductStatusAdapterTest extends \PHPUnit_Framework_TestCase
{

    private $productStatusAdapter;
    private $command;
    private $mockInput;
    private $mockOutput;
    private $mockProductRepository;
    private $mockSearchCriteriaBuilder;
    private $mockSearchResult;

    protected function setUp()
    {
        $this->mockSearchResult = $this->getMock(ProductSearchResultsInterface::class);
        $this->mockSearchCriteriaBuilder = $this->getMock(SearchCriteriaBuilder::class, [], [], '', false);
        $this->mockProductRepository = $this->getMock(ProductRepositoryInterface::class);
        $this->mockProductRepository->method('getList')->willReturn($this->mockSearchResult);
        $this->mockSearchCriteriaBuilder->method('create')
            ->willReturn($this->getMock(SearchCriteria::class, [], [], '', false));

        $this->mockAppState = $this->getMock(AppState::class, [], [], '', false);

        $this->productStatusAdapter = new ProductStatusAdapter(
            $this->mockProductRepository,
            $this->mockSearchCriteriaBuilder,
            $this->mockAppState
        );

        $this->command = new ProductStatusCommand($this->productStatusAdapter);
        $this->mockInput = $this->getMock(InputInterface::class);
        $this->mockOutput = $this->getMock(OutputInterface::class);
    }

    public function testItImplementsProductStatusAdapterInterface()
    {
        $this->assertTrue($this->productStatusAdapter instanceof ProductStatusAdapterInterface);
    }

    public function testGetStatusMethodThrowsAnExceptionIfSkuIsNull()
    {
        $this->setExpectedException(InvalidSkuException::class, 'Sku cannot be null');
        $this->productStatusAdapter->getProductStatusMatchingSku(null);
    }

    public function testGetStatusMethodThrowsAnExceptionIfSkuIsEmpty()
    {
        $sku = '';
        $this->setExpectedException(InvalidSkuException::class, 'Sku cannot be empty');
        $this->productStatusAdapter->getProductStatusMatchingSku($sku);
    }

    public function testGetStatusMethodThrowsAnExceptionIfSkuIsAObject()
    {
        $sku = $this->mockOutput;
        $this->setExpectedException(InvalidSkuException::class, 'Sku must be a string');
        $this->productStatusAdapter->getProductStatusMatchingSku($sku);
    }

    public function testItQueriesAProductRepository()
    {
        $this->mockSearchResult->expects($this->once())->method('getItems')->willReturn([]);
        $this->productStatusAdapter->getProductStatusMatchingSku('testSku');
    }

    public function testItReturnsAnEmptyArrayIfThereIsNoMatch()
    {
        $this->mockSearchResult->expects($this->once())->method('getItems')->willReturn([]);
        $results = $this->productStatusAdapter->getProductStatusMatchingSku('testSku');
        $this->assertSame([], $results);
    }

    public function testItTranslatesTheProductRepositorySearchResultsIntoAStatusArray()
    {
        $this->mockSearchResult->expects($this->once())->method('getItems')->willReturn(
            [
                $this->createMockEnabledProduct('test1'),
                $this->createMockDisabledProduct('test2')
            ]
        );

        $expected = [
            'test1' => 'enabled',
            'test2' => 'disabled'
        ];

        $this->assertSame($expected, $this->productStatusAdapter->getProductStatusMatchingSku('testSku'));
    }

    public function testItAddsTheSkuAsSearchCriteria()
    {
        //don't really understand whats being tested here
        $this->mockSearchCriteriaBuilder->expects($this->once())->method('addFilter')->with('sku', '%test%', 'like');
        $this->mockSearchResult->expects($this->once())->method('getItems')->willReturn([]);
        $this->productStatusAdapter->getProductStatusMatchingSku('test');
    }

    public function testDisbleMethodThrowsAnExceptionIfSkuIsNull()
    {
        $this->setExpectedException(InvalidSkuException::class, 'Sku cannot be null');
        $this->productStatusAdapter->disableProductBySku(null);
    }


    public function testDisbleMethodThrowsAnExceptionIfSkuIsEmpty()
    {
        $sku = '';
        $this->setExpectedException(InvalidSkuException::class, 'Sku cannot be empty');
        $this->productStatusAdapter->disableProductBySku($sku);
    }

    public function testDisbleMethodThrowsAnExceptionIfSkuIsAObject()
    {
        $sku = $this->mockOutput;
        $this->setExpectedException(InvalidSkuException::class, 'Sku must be a string');
        $this->productStatusAdapter->disableProductBySku($sku);
    }

    public function testItThrowsAnExceptionIfProductIsAlreadyDisabled()
    {
        $this->setExpectedException(ProductAlreadyDisabledException::class, 'The product "test" is already disabled');
        $this->mockProductRepository->method('get')->willReturn($this->createMockDisabledProduct('test'));
        $this->productStatusAdapter->disableproductBySku('test');
    }

    public function testItDisablesAnExistingProduct()
    {
        $mockProduct = $this->createMockEnabledProduct('test');
        $mockProduct->expects($this->once())->method('setStatus')->with(ProductStatus::STATUS_DISABLED);
        $this->mockProductRepository->method('get')->willReturn($mockProduct);
        $this->mockProductRepository->expects($this->once())->method('save')->with($mockProduct);
        $this->productStatusAdapter->disableproductBySku('test');

    }

    public function testDisableMethodConversNotFoundExceptionToProductStatusCommandsException()
    {
        $testException = new NoSuchEntityException();
        $this->mockProductRepository->method('get')
            ->willThrowException($testException);
        $this->setExpectedException(ProductStatusAdapterException::class);
        $this->productStatusAdapter->disableproductBySku('test');
    }

    public function testEnableMethodThrowsAnExceptionIfSkuIsNull()
    {
        $this->setExpectedException(InvalidSkuException::class, 'Sku cannot be null');
        $this->productStatusAdapter->enableProductBySku(null);
    }

    public function testEnableMethodThrowsAnExceptionIfSkuIsEmpty()
    {
        $sku = '';
        $this->setExpectedException(InvalidSkuException::class, 'Sku cannot be empty');
        $this->productStatusAdapter->enableProductBySku($sku);
    }

    public function testEnableMethodThrowsAnExceptionIfSkuIsAObject()
    {
        $sku = $this->mockOutput;
        $this->setExpectedException(InvalidSkuException::class, 'Sku must be a string');
        $this->productStatusAdapter->enableProductBySku($sku);
    }

    public function testEnableThrowsAnExceptionIfAllreadyEnabled()
    {
        $this->setExpectedException(ProductAlreadyDisabledException::class, 'The product "test" is already enabled');
        $this->mockProductRepository->method('get')->willReturn($this->createMockEnabledProduct('test'));
        $this->productStatusAdapter->enableProductBySku('test');
    }

    public function testItEnablesAnExistingProduct()
    {
        $mockProduct = $this->createMockDisabledProduct('test');
        $mockProduct->expects($this->once())->method('setStatus')->with(ProductStatus::STATUS_ENABLED);
        $this->mockProductRepository->method('get')->willReturn($mockProduct);
        $this->mockProductRepository->expects($this->once())->method('save')->with($mockProduct);
        $this->productStatusAdapter->enableProductBySku('test');

    }

    public function testEnabledMethodConvertsNotFoundExceptionToProductStatusCommandsException()
    {
        $testException = new NoSuchEntityException();
        $this->mockProductRepository->method('get')
            ->willThrowException($testException);
        $this->setExpectedException(ProductStatusAdapterException::class);
        $this->productStatusAdapter->enableProductBySku('test');
    }

    public function testGetStatusBySkuMethodThrowsAnExceptionIfSkuIsNull()
    {
        $this->setExpectedException(InvalidSkuException::class, 'Sku cannot be null');
        $this->productStatusAdapter->getStatusBySku(null);
    }

    public function testGetStatusBySkuMethodThrowsAnExceptionIfSkuIsEmpty()
    {
        $sku = '';
        $this->setExpectedException(InvalidSkuException::class, 'Sku cannot be empty');
        $this->productStatusAdapter->getStatusBySku($sku);
    }

    public function testGetStatusBySkuMethodThrowsAnExceptionIfSkuIsAObject()
    {
        $sku = $this->mockOutput;
        $this->setExpectedException(InvalidSkuException::class, 'Sku must be a string');
        $this->productStatusAdapter->getStatusBySku($sku);
    }

    public function testItReturnsTheProductsStatusString()
    {
        $this->mockProductRepository->expects($this->once())->method('get')->willReturn(
                $this->createMockEnabledProduct('test1')
        );

        $this->assertSame(ProductStatusAdapterInterface::ENABLED,$this->productStatusAdapter->getStatusBySku('test1'));
    }

    private function createMockEnabledProduct($sku)
    {
        return $this->createMockProductWithStatus($sku, ProductStatus::STATUS_ENABLED);
    }

    private function createMockDisabledProduct($sku)
    {
        return $this->createMockProductWithStatus($sku, ProductStatus::STATUS_DISABLED);
    }

    private function createMockProduct($sku)
    {
        $mockProduct = $this->getMock(ProductInterface::class);
        $mockProduct->method('getSku')->willReturn($sku);
        return $mockProduct;
    }

    private function createMockProductWithStatus($sku, $status)
    {
        $mockProduct = $this->createMockProduct($sku);
        $mockProduct->method('getStatus')->willReturn($status);
        return $mockProduct;
    }
}
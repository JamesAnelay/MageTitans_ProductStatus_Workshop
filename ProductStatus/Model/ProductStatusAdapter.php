<?php

namespace MageTitans\ProductStatus\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\NoSuchEntityException;
use MageTitans\ProductStatus\Model\Exception\InvalidSkuException;
use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;
use MageTitans\ProductStatus\Model\Exception\ProductAlreadyDisabledException;
use MageTitans\ProductStatus\Model\Exception\ProductStatusAdapterException;
use Magento\Framework\App\State as AppState;

class ProductStatusAdapter implements ProductStatusAdapterInterface
{
    private $searchCriteriaBuilder;
    private $productRepository;
    private $appState;

    private $statusMap = [
        ProductStatus::STATUS_ENABLED => ProductStatusAdapterInterface::ENABLED,
        ProductStatus::STATUS_DISABLED => ProductStatusAdapterInterface::DISABLED
    ];

    public function __construct(
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        AppState $appState
    ){
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        try {
            $appState->setAreaCode('adminhtml');
        } catch (\Exception $exception) {};
    }

    public function getProductStatusMatchingSku($sku){
        $this->validateSku($sku);
        $this->searchCriteriaBuilder->addFilter('sku', '%' . $sku . '%', 'like');
        $result = $this->productRepository->getList($this->searchCriteriaBuilder->create());

        $items = $result->getItems();
        $resultsArray = [];

        foreach($items as $product)
        {
            $resultsArray[$product->getSku()] = $this->getMappedStatus($product->getStatus());
        }

        return $resultsArray;
    }

    public function disableProductBySku($sku)
    {
        try {
            $this->validateSku($sku);
            $product = $this->productRepository->get($sku);
            if ($product->getStatus() == ProductStatus::STATUS_DISABLED) {
                throw new ProductAlreadyDisabledException(sprintf('The product "%s" is already disabled', $sku));
            }
            $product->setStatus(ProductStatus::STATUS_DISABLED);
            $this->productRepository->save($product);
        } catch (NoSuchEntityException $e){
            throw new ProductStatusAdapterException($e->getMessage());
        }
    }

    public function enableProductBySku($sku)
    {
        try{
            $this->validateSku($sku);
            $product = $this->productRepository->get($sku);
            if ($product->getStatus() == ProductStatus::STATUS_ENABLED)
            {
                throw new ProductAlreadyDisabledException(sprintf('The product "%s" is already enabled', $sku));
            }
            $product->setStatus(ProductStatus::STATUS_ENABLED);
            $this->productRepository->save($product);
        } catch (NoSuchEntityException $e){
            throw new ProductStatusAdapterException($e->getMessage());
        }

    }

    public function getStatusBySku($sku)
    {
        $this->validateSku($sku);
        $product = $this->productRepository->get($sku);
        return $this->getMappedStatus($product->getStatus());
    }

    private function getMappedStatus($status)
    {
        return $this->statusMap[$status];
    }

    private function validateSku($sku){
        if($sku === null){
            throw new InvalidSkuException('Sku cannot be null');
        }
        if(empty($sku)){
            throw new InvalidSkuException('Sku cannot be empty');
        }
        if(!is_string($sku)){
            throw new InvalidSkuException('Sku must be a string');
        }
    }
}
<?php

namespace MageTitans\ProductStatus\Model;

use MageTitans\ProductStatus\Api\ProductStatusManagementInterface;
use MageTitans\ProductStatus\Model\Exception;
use MageTitans\ProductStatus\Model\Exception\ProductAlreadyDisabledException;

class ProductStatusManagement implements ProductStatusManagementInterface
{
    private $productStatusAdapter;

    public function __construct(ProductStatusAdapterInterface $productStatusAdapter)
    {
        $this->productStatusAdapter = $productStatusAdapter;
    }

    /**
     * {@inheritdoc}
     */
    public function get($sku)
    {
        return $this->productStatusAdapter->getStatusBySku($sku);
    }

    /**
     * {@inheritdoc}
     */
    public function set($sku, $status){
        switch($status) {
            case ProductStatusAdapterInterface::ENABLED:
                $this->enableProductBySku($sku);
                break;
            case ProductStatusAdapterInterface::DISABLED:
                $this->disableProductBySku($sku);
                break;
            default:
                $this->throwInvalidProductStatusException();
        }
        return $status;
    }

    private function enableProductBySku($sku){
        $this->productStatusAdapter->enableProductBySku($sku);
    }

    private function disableProductBySku($sku){
        try{
            $this->productStatusAdapter->disableProductBySku($sku);
        } catch (ProductAlreadyDisabledException $e){

        }
    }

    private function throwInvalidProductStatusException()
    {
        throw new \InvalidArgumentException(sprintf(
            'The product status you entered is not valid - it must match "%s" or "%s"',
            ProductStatusAdapterInterface::ENABLED,
            ProductStatusAdapterInterface::DISABLED
        ));
    }
}
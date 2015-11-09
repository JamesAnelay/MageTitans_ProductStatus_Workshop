<?php

namespace MageTitans\ProductStatus\Model;

interface ProductStatusAdapterInterface
{
    const ENABLED = 'enabled';
    const DISABLED = 'disabled';

    public function getProductStatusMatchingSku($sku);
    public function disableProductBySku($sku);
    public function enableProductBySku($sku);
    public function getStatusBySku($sku);
}
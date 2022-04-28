<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\ProductStorage\Finder;

use Generated\Shared\Transfer\ProductViewTransfer;
use Spryker\Client\ProductStorage\Dependency\Client\ProductStorageToStoreClientInterface;
use Spryker\Client\ProductStorage\Mapper\ProductStorageDataMapperInterface;
use Spryker\Client\ProductStorage\Storage\ProductAbstractStorageReaderInterface;

class ProductAbstractViewTransferFinder extends AbstractProductViewTransferFinder
{
    /**
     * @var string
     */
    protected const KEY_ID_PRODUCT = 'id_product_abstract';

    /**
     * @var \Spryker\Client\ProductStorage\Storage\ProductAbstractStorageReaderInterface
     */
    protected $productAbstractStorageReader;

    /**
     * @var array
     */
    protected static $productViewTransfersCache = [];

    /**
     * @var \Spryker\Client\ProductStorage\Dependency\Client\ProductStorageToStoreClientInterface
     */
    protected $storeClient;

    /**
     * @param \Spryker\Client\ProductStorage\Storage\ProductAbstractStorageReaderInterface $productAbstractStorage
     * @param \Spryker\Client\ProductStorage\Mapper\ProductStorageDataMapperInterface $productStorageDataMapper
     * @param \Spryker\Client\ProductStorage\Dependency\Client\ProductStorageToStoreClientInterface $storeClient
     */
    public function __construct(
        ProductAbstractStorageReaderInterface $productAbstractStorage,
        ProductStorageDataMapperInterface $productStorageDataMapper,
        ProductStorageToStoreClientInterface $storeClient
    ) {
        parent::__construct($productStorageDataMapper);
        $this->productAbstractStorageReader = $productAbstractStorage;
        $this->storeClient = $storeClient;
    }

    /**
     * @param int $idProduct ID product abstract.
     * @param string $localeName
     *
     * @return array|null
     */
    protected function findProductStorageData(int $idProduct, string $localeName): ?array
    {
        return $this->productAbstractStorageReader->findProductAbstractStorageData($idProduct, $localeName);
    }

    /**
     * @param array<int> $productIds
     * @param string $localeName
     *
     * @return array
     */
    protected function getBulkProductStorageData(array $productIds, string $localeName): array
    {
        $storeName = $this->storeClient->getCurrentStore()->getName();

        return $this
            ->productAbstractStorageReader
            ->getBulkProductAbstractStorageDataByProductAbstractIdsForLocaleNameAndStore($productIds, $localeName, $storeName);
    }

    /**
     * @param \Generated\Shared\Transfer\ProductViewTransfer $productViewTransfer
     *
     * @return int
     */
    protected function getProductId(ProductViewTransfer $productViewTransfer): int
    {
        return $productViewTransfer->getIdProductAbstract();
    }

    /**
     * @param array<string, mixed> $productData
     *
     * @return int
     */
    protected function getProductDataProductId(array $productData): int
    {
        return $productData[static::KEY_ID_PRODUCT];
    }
}

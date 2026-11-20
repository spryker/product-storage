<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\ProductStorage\Expander;

use Generated\Shared\Transfer\ProductViewTransfer;
use Spryker\Client\ProductStorage\Dependency\Client\ProductStorageToStoreClientInterface;
use Spryker\Client\ProductStorage\Resolver\ProductBrandResolverInterface;
use Spryker\Client\ProductStorage\Resolver\ProductSelectedVariantAttributesResolverInterface;
use Spryker\Client\ProductStorage\Storage\ProductAbstractStorageReaderInterface;

class ProductViewProductAbstractExpander implements ProductViewProductAbstractExpanderInterface
{
    protected const string KEY_SKU = 'sku';

    protected const string KEY_NAME = 'name';

    protected const string KEY_ATTRIBUTES = 'attributes';

    public function __construct(
        protected ProductAbstractStorageReaderInterface $productAbstractStorageReader,
        protected ProductStorageToStoreClientInterface $storeClient,
        protected ProductSelectedVariantAttributesResolverInterface $productSelectedVariantAttributesResolver,
        protected ProductBrandResolverInterface $productBrandResolver
    ) {
    }

    /**
     * @param array<\Generated\Shared\Transfer\ProductViewTransfer> $productViewTransfers
     * @param string $localeName
     *
     * @return array<\Generated\Shared\Transfer\ProductViewTransfer>
     */
    public function expandProductViewTransfersWithProductAbstract(array $productViewTransfers, string $localeName): array
    {
        return $this->expandProductViewTransfersWithProductAbstractStorageData($productViewTransfers, $localeName);
    }

    /**
     * @param array<\Generated\Shared\Transfer\ProductViewTransfer> $productViewTransfers
     *
     * @return array<\Generated\Shared\Transfer\ProductViewTransfer>
     */
    protected function expandProductViewTransfersWithProductAbstractStorageData(array $productViewTransfers, string $localeName): array
    {
        $productAbstractStorageDataCollection = $this->getProductAbstractStorageDataIndexedByIdProductAbstract($productViewTransfers, $localeName);

        foreach ($productViewTransfers as $productViewTransfer) {
            // A list may build its product views from storage data by hand, so they have not been through the storage data mapper.
            // A brand already set, by the mapper or a product view expander plugin, is kept.
            if ($productViewTransfer->getBrand() === null) {
                $productViewTransfer->setBrand($this->productBrandResolver->findBrand($productViewTransfer->getAttributes()));
            }

            $idProductAbstract = $productViewTransfer->getIdProductAbstract();

            if (!$idProductAbstract || !isset($productAbstractStorageDataCollection[$idProductAbstract])) {
                continue;
            }

            $productAbstractStorageData = $productAbstractStorageDataCollection[$idProductAbstract];

            $productViewTransfer->setAbstractSku($productAbstractStorageData[static::KEY_SKU] ?? null);
            $productViewTransfer->setAbstractName($productAbstractStorageData[static::KEY_NAME] ?? null);
            $productViewTransfer->setSelectedVariantAttributes($this->getSelectedVariantAttributes($productViewTransfer, $productAbstractStorageData));

            if ($productViewTransfer->getBrand() === null) {
                $productViewTransfer->setBrand($this->productBrandResolver->findBrand($productAbstractStorageData[static::KEY_ATTRIBUTES] ?? []));
            }
        }

        return $productViewTransfers;
    }

    /**
     * @param array<string, mixed> $productAbstractStorageData
     *
     * @return array<string, string>
     */
    protected function getSelectedVariantAttributes(ProductViewTransfer $productViewTransfer, array $productAbstractStorageData): array
    {
        // Without a selected concrete product the attributes are the abstract ones, and an abstract value of a super attribute selects no variant.
        if (!$productViewTransfer->getIdProductConcrete()) {
            return [];
        }

        return $this->productSelectedVariantAttributesResolver->resolveSelectedVariantAttributes($productAbstractStorageData, $productViewTransfer->getAttributes());
    }

    /**
     * @param array<\Generated\Shared\Transfer\ProductViewTransfer> $productViewTransfers
     * @param string $localeName
     *
     * @return array<int, array<string, mixed>>
     */
    protected function getProductAbstractStorageDataIndexedByIdProductAbstract(array $productViewTransfers, string $localeName): array
    {
        $productAbstractIds = [];

        foreach ($productViewTransfers as $productViewTransfer) {
            $idProductAbstract = $productViewTransfer->getIdProductAbstract();

            if ($idProductAbstract) {
                $productAbstractIds[$idProductAbstract] = $idProductAbstract;
            }
        }

        if (!$productAbstractIds) {
            return [];
        }

        return $this->productAbstractStorageReader->getBulkProductAbstractStorageDataByProductAbstractIdsForLocaleNameAndStore(
            array_values($productAbstractIds),
            $localeName,
            $this->storeClient->getCurrentStore()->getNameOrFail(),
        );
    }
}

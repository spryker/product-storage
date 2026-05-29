<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\ProductStorage\Plugin\Catalog;

use Generated\Shared\Transfer\ProductConcreteCriteriaFilterTransfer;
use Spryker\Client\CatalogExtension\Dependency\Plugin\ProductConcreteStorageSearchPluginInterface;
use Spryker\Client\Kernel\AbstractPlugin;

/**
 * @method \Spryker\Client\ProductStorage\ProductStorageFactory getFactory()
 */
class ProductConcreteStorageSearchPlugin extends AbstractPlugin implements ProductConcreteStorageSearchPluginInterface
{
    public function isApplicable(ProductConcreteCriteriaFilterTransfer $productConcreteCriteriaFilterTransfer): bool
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function searchProductConcretes(ProductConcreteCriteriaFilterTransfer $productConcreteCriteriaFilterTransfer): array
    {
        return $this->getFactory()
            ->createProductConcreteStorageCatalogSearcher()
            ->searchProductConcretes($productConcreteCriteriaFilterTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @param array<array<mixed>> $abstractSearchResults
     */
    public function searchProductConcretesByAbstractSearchResults(array $abstractSearchResults): array
    {
        return $this->getFactory()
            ->createProductConcreteStorageCatalogSearcher()
            ->searchProductConcretesByAbstractSearchResults($abstractSearchResults);
    }
}

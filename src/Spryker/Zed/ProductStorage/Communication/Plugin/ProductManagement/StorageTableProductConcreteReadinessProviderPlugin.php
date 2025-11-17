<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductStorage\Communication\Plugin\ProductManagement;

use ArrayObject;
use Generated\Shared\Transfer\ProductConcreteReadinessRequestTransfer;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use Spryker\Zed\ProductManagementExtension\Dependency\Plugin\ProductConcreteReadinessProviderPluginInterface;

/**
 * @method \Spryker\Zed\ProductStorage\Business\ProductStorageBusinessFactory getBusinessFactory()
 * @method \Spryker\Zed\ProductStorage\ProductStorageConfig getConfig()
 */
class StorageTableProductConcreteReadinessProviderPlugin extends AbstractPlugin implements ProductConcreteReadinessProviderPluginInterface
{
    /**
     * Specification:
     * {@inheritDoc}
     * - Expands product readiness collection with storage status check.
     * - Shows locales for which records exist in spy_product_concrete_storage table.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\ProductConcreteReadinessRequestTransfer $productConcreteReadinessRequestTransfer
     * @param \ArrayObject<int, \Generated\Shared\Transfer\ProductReadinessTransfer> $productReadinessTransfers
     *
     * @return \ArrayObject<int, \Generated\Shared\Transfer\ProductReadinessTransfer>
     */
    public function provide(
        ProductConcreteReadinessRequestTransfer $productConcreteReadinessRequestTransfer,
        ArrayObject $productReadinessTransfers
    ): ArrayObject {
        return $this->getBusinessFactory()
            ->createStorageTableProductConcreteReadinessProvider()
            ->provide($productConcreteReadinessRequestTransfer, $productReadinessTransfers);
    }
}

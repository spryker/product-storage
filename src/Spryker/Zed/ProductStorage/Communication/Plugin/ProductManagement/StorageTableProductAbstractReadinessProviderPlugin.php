<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductStorage\Communication\Plugin\ProductManagement;

use ArrayObject;
use Generated\Shared\Transfer\ProductAbstractReadinessRequestTransfer;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use Spryker\Zed\ProductManagementExtension\Dependency\Plugin\ProductAbstractReadinessProviderPluginInterface;

/**
 * @method \Spryker\Zed\ProductStorage\Business\ProductStorageBusinessFactory getBusinessFactory()
 * @method \Spryker\Zed\ProductStorage\ProductStorageConfig getConfig()
 */
class StorageTableProductAbstractReadinessProviderPlugin extends AbstractPlugin implements ProductAbstractReadinessProviderPluginInterface
{
    /**
     * Specification:
     * {@inheritDoc}
     * - Expands product readiness collection with storage status check.
     * - Shows store/locale combinations for which records exist in spy_product_abstract_storage table.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\ProductAbstractReadinessRequestTransfer $productAbstractReadinessRequestTransfer
     * @param \ArrayObject<int, \Generated\Shared\Transfer\ProductReadinessTransfer> $productReadinessTransfers
     *
     * @return \ArrayObject<int, \Generated\Shared\Transfer\ProductReadinessTransfer>
     */
    public function provide(
        ProductAbstractReadinessRequestTransfer $productAbstractReadinessRequestTransfer,
        ArrayObject $productReadinessTransfers
    ): ArrayObject {
        return $this->getBusinessFactory()
            ->createStorageTableProductAbstractReadinessProvider()
            ->provide($productAbstractReadinessRequestTransfer, $productReadinessTransfers);
    }
}

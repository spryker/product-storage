<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductStorage\Communication\Plugin\Event\Listener;

use Orm\Zed\Product\Persistence\Map\SpyProductAbstractLocalizedAttributesTableMap;
use Spryker\Zed\Event\Dependency\Plugin\EventBulkHandlerInterface;

/**
 * @method \Spryker\Zed\ProductStorage\Persistence\ProductStorageQueryContainerInterface getQueryContainer()
 * @method \Spryker\Zed\ProductStorage\Communication\ProductStorageCommunicationFactory getFactory()
 * @method \Spryker\Zed\ProductStorage\Business\ProductStorageFacadeInterface getFacade()
 * @method \Spryker\Zed\ProductStorage\ProductStorageConfig getConfig()
 */
class ProductAbstractLocalizedAttributesStorageListener extends AbstractProductStorageListener implements EventBulkHandlerInterface
{
    /**
     * @api
     *
     * @param array<\Generated\Shared\Transfer\EventEntityTransfer> $eventEntityTransfers
     * @param string $eventName
     *
     * @return void
     */
    public function handleBulk(array $eventEntityTransfers, $eventName)
    {
        $productAbstractIds = $this->getFactory()->getEventBehaviorFacade()->getEventTransferForeignKeys(
            $eventEntityTransfers,
            SpyProductAbstractLocalizedAttributesTableMap::COL_FK_PRODUCT_ABSTRACT,
        );

        $this->publishAbstractProducts($productAbstractIds);
    }
}

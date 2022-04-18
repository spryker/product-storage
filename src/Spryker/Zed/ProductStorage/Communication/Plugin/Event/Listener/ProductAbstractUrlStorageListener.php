<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductStorage\Communication\Plugin\Event\Listener;

use Orm\Zed\Url\Persistence\Map\SpyUrlTableMap;
use Spryker\Zed\Event\Dependency\Plugin\EventBulkHandlerInterface;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;

/**
 * @method \Spryker\Zed\ProductStorage\Persistence\ProductStorageQueryContainerInterface getQueryContainer()
 * @method \Spryker\Zed\ProductStorage\Communication\ProductStorageCommunicationFactory getFactory()
 * @method \Spryker\Zed\ProductStorage\Business\ProductStorageFacadeInterface getFacade()
 * @method \Spryker\Zed\ProductStorage\ProductStorageConfig getConfig()
 */
class ProductAbstractUrlStorageListener extends AbstractPlugin implements EventBulkHandlerInterface
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
        $productAbstractIds = $this->getValidProductIds($eventEntityTransfers);
        if (!$productAbstractIds) {
            return;
        }

        $this->getFacade()->publishAbstractProducts($productAbstractIds);
    }

    /**
     * @param array<\Generated\Shared\Transfer\EventEntityTransfer> $eventTransfers
     *
     * @return array
     */
    protected function getValidProductIds(array $eventTransfers)
    {
        $validEventTransfers = [];
        foreach ($eventTransfers as $eventTransfer) {
            if (
                in_array(SpyUrlTableMap::COL_URL, $eventTransfer->getModifiedColumns()) ||
                in_array(SpyUrlTableMap::COL_FK_RESOURCE_PRODUCT_ABSTRACT, $eventTransfer->getModifiedColumns())
            ) {
                $validEventTransfers[] = $eventTransfer;
            }
        }

        return $this->getFactory()->getEventBehaviorFacade()->getEventTransferForeignKeys(
            $validEventTransfers,
            SpyUrlTableMap::COL_FK_RESOURCE_PRODUCT_ABSTRACT,
        );
    }
}

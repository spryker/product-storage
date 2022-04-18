<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductStorage\Communication\Plugin\Event\Listener;

use Spryker\Zed\Event\Dependency\Plugin\EventBulkHandlerInterface;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use Spryker\Zed\Product\Dependency\ProductEvents;

/**
 * @deprecated Use {@link \Spryker\Zed\ProductStorage\Communication\Plugin\Event\Listener\ProductAbstractStoragePublishListener} and {@link \Spryker\Zed\ProductStorage\Communication\Plugin\Event\Listener\ProductAbstractStorageUnpublishListener} instead.
 *
 * @method \Spryker\Zed\ProductStorage\Persistence\ProductStorageQueryContainerInterface getQueryContainer()
 * @method \Spryker\Zed\ProductStorage\Communication\ProductStorageCommunicationFactory getFactory()
 * @method \Spryker\Zed\ProductStorage\Business\ProductStorageFacadeInterface getFacade()
 * @method \Spryker\Zed\ProductStorage\ProductStorageConfig getConfig()
 */
class ProductAbstractStorageListener extends AbstractPlugin implements EventBulkHandlerInterface
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
        $productAbstractIds = $this->getFactory()->getEventBehaviorFacade()->getEventTransferIds($eventEntityTransfers);

        if (
            $eventName === ProductEvents::ENTITY_SPY_PRODUCT_ABSTRACT_DELETE ||
            $eventName === ProductEvents::PRODUCT_ABSTRACT_UNPUBLISH
        ) {
            $this->getFacade()->unpublishProductAbstracts($productAbstractIds);
        } else {
            $this->getFacade()->publishAbstractProducts($productAbstractIds);
        }
    }
}

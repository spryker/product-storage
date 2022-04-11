<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductStorage\Business\StorageExpander;

use Generated\Shared\Transfer\ProductAbstractStorageTransfer;
use Spryker\Zed\ProductStorage\Dependency\Facade\ProductStorageToProductInterface;

class UpcsProductAbstractStorageExpander implements UpcsProductAbstractStorageExpanderInterface
{
    /**
     * @var string
     */
    protected const ATTRIBUTE_UPCS = 'upcs';

    /**
     * @var \Spryker\Zed\ProductStorage\Dependency\Facade\ProductStorageToProductInterface
     */
    protected $productFacade;

    /**
     * @param \Spryker\Zed\ProductStorage\Dependency\Facade\ProductStorageToProductInterface $productFacade
     */
    public function __construct(ProductStorageToProductInterface $productFacade)
    {
        $this->productFacade = $productFacade;
    }

    /**
     * @param \Generated\Shared\Transfer\ProductAbstractStorageTransfer $productAbstractStorageTransfer
     *
     * @return \Generated\Shared\Transfer\ProductAbstractStorageTransfer
     */
    public function expand(
        ProductAbstractStorageTransfer $productAbstractStorageTransfer
    ): ProductAbstractStorageTransfer {
        $upcs = $this->getConcreteProductsUpcs($productAbstractStorageTransfer);

        $attributes = $productAbstractStorageTransfer->getAttributes();
        $attributes[static::ATTRIBUTE_UPCS] = $upcs;
        $productAbstractStorageTransfer->setAttributes($attributes);

        return $productAbstractStorageTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\ProductAbstractStorageTransfer $productAbstractStorageTransfer
     *
     * @return string
     */
    protected function getConcreteProductsUpcs(ProductAbstractStorageTransfer $productAbstractStorageTransfer): string
    {
        $productConcreteTransfers = $this->productFacade
            ->getConcreteProductsByAbstractProductId((int)$productAbstractStorageTransfer->getIdProductAbstract());

        $upcs = [];
        foreach ($productConcreteTransfers as $productConcreteTransfer) {
            if (!$productConcreteTransfer->getIsActive()) {
                continue;
            }

            $attributes = $productConcreteTransfer->getAttributes();
            if (isset($attributes[static::ATTRIBUTE_UPCS])) {
                $upcs[] = $attributes[static::ATTRIBUTE_UPCS];
            }
        }

        return implode(',', $upcs);
    }
}

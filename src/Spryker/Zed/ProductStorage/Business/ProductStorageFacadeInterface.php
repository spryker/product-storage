<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductStorage\Business;

use Generated\Shared\Transfer\ProductAbstractStorageTransfer;

interface ProductStorageFacadeInterface
{
    /**
     * Specification:
     * - TODO: Add method specification.
     *
     * @api
     *
     * @param array<int> $productAbstractIds
     *
     * @return void
     */
    public function publishAbstractProducts(array $productAbstractIds);

    /**
     * Specification:
     * - TODO: Add method specification.
     *
     * @api
     *
     * @param array<int> $productAbstractIds
     *
     * @return void
     */
    public function unpublishProductAbstracts(array $productAbstractIds);

    /**
     * Specification:
     * - Finds product concrete localized entities by product abstract ids.
     * - Finds product concrete storage entities by product abstract ids.
     * - Deletes product concrete storage entities if no localized entities are found.
     * - Expands ProductConcreteStorageTransfer collection with a stack of `ProductConcreteStorageCollectionExpanderPluginInterface`.
     * - Publishes product concrete storage collection to storage.
     *
     * @api
     *
     * @param array $productIds
     *
     * @return void
     */
    public function publishConcreteProducts(array $productIds);

    /**
     * Specification:
     * - TODO: Add method specification.
     *
     * @api
     *
     * @param array $productIds
     *
     * @return void
     */
    public function unpublishConcreteProducts(array $productIds);

    /**
     * Specification:
     * - Expands and returns the provided ProductAbstractStorage transfer object.
     * - Expands the provided ProductAbstractStorage transfer object with upcs from concrete products.
     * - Sets ProductAbstractStorage upcs attribute from related concrete products' upcs.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\ProductAbstractStorageTransfer $productAbstractStorageTransfer
     *
     * @return \Generated\Shared\Transfer\ProductAbstractStorageTransfer
     */
    public function expandUpcsProductAbstractStorage(
        ProductAbstractStorageTransfer $productAbstractStorageTransfer
    ): ProductAbstractStorageTransfer;
}

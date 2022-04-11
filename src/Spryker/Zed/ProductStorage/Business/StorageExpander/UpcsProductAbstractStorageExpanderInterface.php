<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductStorage\Business\StorageExpander;

use Generated\Shared\Transfer\ProductAbstractStorageTransfer;

interface UpcsProductAbstractStorageExpanderInterface
{
    /**
     * @param \Generated\Shared\Transfer\ProductAbstractStorageTransfer $productAbstractStorageTransfer
     *
     * @return \Generated\Shared\Transfer\ProductAbstractStorageTransfer
     */
    public function expand(ProductAbstractStorageTransfer $productAbstractStorageTransfer): ProductAbstractStorageTransfer;
}

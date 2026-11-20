<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\ProductStorage\Expander;

interface ProductViewProductAbstractExpanderInterface
{
    /**
     * @param array<\Generated\Shared\Transfer\ProductViewTransfer> $productViewTransfers
     * @param string $localeName
     *
     * @return array<\Generated\Shared\Transfer\ProductViewTransfer>
     */
    public function expandProductViewTransfersWithProductAbstract(array $productViewTransfers, string $localeName): array;
}

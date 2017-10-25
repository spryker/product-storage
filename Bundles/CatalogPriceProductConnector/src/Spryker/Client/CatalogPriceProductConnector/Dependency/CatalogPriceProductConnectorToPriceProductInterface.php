<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\CatalogPriceProductConnector\Dependency;

interface CatalogPriceProductConnectorToPriceProductInterface
{
    /**
     * @param array $priceMap
     *
     * @return \Generated\Shared\Transfer\StorageProductTransfer
     */
    public function resolveProductPrice(array $priceMap);
}

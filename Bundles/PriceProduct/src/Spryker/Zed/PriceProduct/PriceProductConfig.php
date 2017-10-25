<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\PriceProduct;

use Spryker\Shared\PriceProduct\PriceProductConfig as SharedPriceProductConfig;
use Spryker\Zed\Kernel\AbstractBundleConfig;

class PriceProductConfig extends AbstractBundleConfig
{
    /**
     * @return \Spryker\Shared\PriceProduct\PriceProductConfig
     */
    public function createSharedPriceConfig()
    {
        return new SharedPriceProductConfig();
    }

    /**
     * @return string
     */
    public function getPriceTypeDefaultName()
    {
        return $this->createSharedPriceConfig()->getPriceTypeDefaultName();
    }
}

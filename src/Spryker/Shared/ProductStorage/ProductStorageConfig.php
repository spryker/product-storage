<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Shared\ProductStorage;

use Spryker\Shared\Kernel\AbstractSharedConfig;

class ProductStorageConfig extends AbstractSharedConfig
{
    /**
     * Defines queue name for publish.
     *
     * @var string
     */
    public const PUBLISH_PRODUCT_ABSTRACT = 'publish.product_abstract';

    /**
     * Defines queue name for publish.
     *
     * @var string
     */
    public const PUBLISH_PRODUCT_CONCRETE = 'publish.product_concrete';
}

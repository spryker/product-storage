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
     * @api
     *
     * Defines queue name for publish.
     *
     * @var string
     */
    public const PUBLISH_PRODUCT_ABSTRACT = 'publish.product_abstract';

    /**
     * @api
     *
     * Defines queue name for publish.
     *
     * @var string
     */
    public const PUBLISH_PRODUCT_CONCRETE = 'publish.product_concrete';

    public const string PRODUCT_ABSTRACT_STORES_MAP = 'product_abstract_stores_map';

    public const string PRODUCT_ABSTRACT_STORAGE_UNIFIED_STORE_KEY = 'product_store_unified';

    /**
     * Specification:
     * - When true, product abstract entries are stored once per locale under a unified store key.
     * - The payload includes product_abstract_stores_map listing which stores carry the product.
     * - Must be enabled consistently in both Zed and Client layers.
     *
     * @api
     */
    public function isProductAbstractStorageUnifiedEnabled(): bool
    {
        return false;
    }
}

<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\ProductStorage;

use Spryker\Client\Kernel\AbstractBundleConfig;

/**
 * @method \Spryker\Shared\ProductStorage\ProductStorageConfig getSharedConfig()
 */
class ProductStorageConfig extends AbstractBundleConfig
{
    /**
     * @uses \Spryker\Shared\Product\ProductConfig::RESOURCE_TYPE_ATTRIBUTE_MAP
     *
     * @var string
     */
    public const RESOURCE_TYPE_ATTRIBUTE_MAP = 'attribute_map';

    /**
     * @uses \Spryker\Shared\Product\ProductConfig::VARIANT_LEAF_NODE_ID
     *
     * @var string
     */
    public const VARIANT_LEAF_NODE_ID = 'id_product_concrete';

    /**
     * @uses \Spryker\Shared\Product\ProductConfig::ATTRIBUTE_MAP_PATH_DELIMITER
     *
     * @phpstan-var non-empty-string
     *
     * @var string
     */
    public const ATTRIBUTE_MAP_PATH_DELIMITER = ':';

    /**
     * To be able to work with data exported with collectors to redis, we need to bring this module into compatibility
     * mode. If this is turned on the ProductClient will be used instead.
     *
     * @api
     *
     * @return bool
     */
    public static function isCollectorCompatibilityMode(): bool
    {
        return false;
    }

    /**
     * Specification:
     * - When true, product abstract data is read from a unified store key instead of a per-store key.
     * - After reading, the current store is verified against product_abstract_stores_map in the payload.
     * - Must match the Zed layer config.
     *
     * @api
     */
    public function isProductAbstractStorageUnifiedEnabled(): bool
    {
        return $this->getSharedConfig()->isProductAbstractStorageUnifiedEnabled();
    }

    /**
     * Specification:
     * - Applies only when `isProductAbstractStorageUnifiedEnabled()` returns true.
     * - When true, the URL mapper verifies that the unified storage key exists in Redis before using it.
     * - If the key is absent (product not yet re-published under the unified schema), the mapper falls
     *   back to the per-store key format so URL resolution stays functional during migration.
     * - Set to false once all products are fully published under unified keys to skip the Redis check.
     *
     * @api
     */
    public function isProductAbstractStorageUnifiedFallbackEnabled(): bool
    {
        return true;
    }
}

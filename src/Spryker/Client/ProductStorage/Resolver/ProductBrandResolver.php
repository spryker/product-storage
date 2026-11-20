<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\ProductStorage\Resolver;

use Spryker\Client\ProductStorage\ProductStorageConfig;

class ProductBrandResolver implements ProductBrandResolverInterface
{
    public function __construct(protected ProductStorageConfig $productStorageConfig)
    {
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function findBrand(array $attributes): ?string
    {
        $brand = $attributes[$this->productStorageConfig->getBrandAttributeKey()] ?? null;
        $hasBrand = is_scalar($brand) && (string)$brand !== '';

        if (!$hasBrand) {
            return null;
        }

        return (string)$brand;
    }
}

<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\ProductStorage\Resolver;

interface ProductBrandResolverInterface
{
    /**
     * @param array<string, mixed> $attributes
     */
    public function findBrand(array $attributes): ?string;
}

<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\ProductStorage\Resolver;

interface ProductSelectedVariantAttributesResolverInterface
{
    /**
     * @param array<string, mixed> $productAbstractStorageData
     * @param array<string, mixed> $concreteAttributes
     *
     * @return array<string, string>
     */
    public function resolveSelectedVariantAttributes(array $productAbstractStorageData, array $concreteAttributes): array;
}

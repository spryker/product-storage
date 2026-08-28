<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\ProductStorage\Resolver;

interface SelectedProductAttributesResolverInterface
{
    /**
     * @param array<string, mixed> $productStorageData
     * @param array<string, mixed> $selectedAttributes
     *
     * @return array<string, mixed>
     */
    public function resolveSelectedProductAttributes(array $productStorageData, array $selectedAttributes, string $localeName): array;
}

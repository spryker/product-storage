<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\ProductStorage\Resolver;

use Spryker\Client\ProductStorage\ProductStorageConfig;

class ProductSelectedVariantAttributesResolver implements ProductSelectedVariantAttributesResolverInterface
{
    protected const string KEY_SUPER_ATTRIBUTES = 'super_attributes';

    /**
     * @param array<string, mixed> $productAbstractStorageData
     * @param array<string, mixed> $concreteAttributes
     *
     * @return array<string, string>
     */
    public function resolveSelectedVariantAttributes(array $productAbstractStorageData, array $concreteAttributes): array
    {
        $selectedVariantAttributes = [];

        foreach ($this->getSuperAttributeKeys($productAbstractStorageData) as $superAttributeKey) {
            $value = $concreteAttributes[$superAttributeKey] ?? null;
            $hasValue = is_scalar($value) && (string)$value !== '';

            if (!$hasValue) {
                continue;
            }

            $selectedVariantAttributes[(string)$superAttributeKey] = (string)$value;
        }

        return $selectedVariantAttributes;
    }

    /**
     * @param array<string, mixed> $productAbstractStorageData
     *
     * @return list<int|string>
     */
    protected function getSuperAttributeKeys(array $productAbstractStorageData): array
    {
        $superAttributeKeys = array_keys($productAbstractStorageData[ProductStorageConfig::RESOURCE_TYPE_ATTRIBUTE_MAP][static::KEY_SUPER_ATTRIBUTES] ?? []);
        // String order, so every concrete product of the abstract product lists its attributes in the same order: PHP turns a numeric attribute key into an integer.
        sort($superAttributeKeys, SORT_STRING);

        return $superAttributeKeys;
    }
}

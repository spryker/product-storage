<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\ProductStorage\Filter;

use Generated\Shared\Transfer\ProductViewTransfer;
use Spryker\Client\ProductStorage\Dependency\Service\ProductStorageToUtilSanitizeServiceInterface;

class ProductAttributeFilter implements ProductAttributeFilterInterface
{
    /**
     * @uses \Spryker\Zed\Product\ProductConfig::ATTRIBUTE_MAP_PATH_DELIMITER
     *
     * @phpstan-var non-empty-string
     *
     * @var string
     */
    protected const ATTRIBUTE_MAP_PATH_DELIMITER = ':';

    /**
     * @var \Spryker\Client\ProductStorage\Dependency\Service\ProductStorageToUtilSanitizeServiceInterface
     */
    protected $utilSanitizeService;

    public function __construct(ProductStorageToUtilSanitizeServiceInterface $utilSanitizeService)
    {
        $this->utilSanitizeService = $utilSanitizeService;
    }

    public function filterAvailableProductAttributes(
        array $selectedVariantNode,
        ProductViewTransfer $productViewTransfer
    ): array {
        if ($productViewTransfer->getAttributeMap()->getAttributeVariantMap()) {
            return $this->getAvailableAttributes($productViewTransfer);
        }

        return $this->findAvailableAttributes($selectedVariantNode);
    }

    protected function getAvailableAttributes(ProductViewTransfer $productViewTransfer): array
    {
        $availableAttributes = [];
        $selectedAttributes = $this->utilSanitizeService->arrayFilterRecursive($productViewTransfer->getSelectedAttributes());

        if (!$selectedAttributes) {
            return [];
        }

        $attributeVariantMap = $productViewTransfer->getAttributeMap()->getAttributeVariantMap();

        foreach ($attributeVariantMap as $productSuperAttributes) {
            if (!$this->isSubsetAttributes($selectedAttributes, $productSuperAttributes)) {
                continue;
            }

            $availableAttributes = $this->filterAvailableAttributes(
                $productSuperAttributes,
                $selectedAttributes,
                $availableAttributes,
            );
        }

        return $availableAttributes;
    }

    /**
     * @deprecated Exists for Backward Compatibility reasons only. Use {@link getAvailableAttributes()} instead.
     *
     * @param array $selectedNode
     * @param array $filteredAttributes
     *
     * @return array
     */
    protected function findAvailableAttributes(array $selectedNode, array $filteredAttributes = [])
    {
        foreach (array_keys($selectedNode) as $attributePath) {
            [$attributeKey, $attributeValue] = explode(static::ATTRIBUTE_MAP_PATH_DELIMITER, $attributePath);
            $filteredAttributes[$attributeKey][] = $attributeValue;
        }

        return $filteredAttributes;
    }

    protected function filterAvailableAttributes(
        array $superAttributes,
        array $selectedAttributes,
        array $availableAttributes
    ): array {
        $attributesToAdd = array_diff_assoc($superAttributes, $selectedAttributes);

        foreach ($attributesToAdd as $attributeKey => $attributeValue) {
            if ($this->hasAttributeWithValue($availableAttributes, $attributeKey, $attributeValue)) {
                continue;
            }

            $availableAttributes[$attributeKey][] = $attributeValue;
        }

        return $availableAttributes;
    }

    protected function isSubsetAttributes(array $selectedAttributes, array $productSuperAttributes): bool
    {
        foreach ($selectedAttributes as $superAttributeKey => $superAttributeValue) {
            if (!$this->includeSameAttribute($productSuperAttributes, $superAttributeKey, $superAttributeValue)) {
                return false;
            }
        }

        return true;
    }

    protected function includeSameAttribute(
        array $superAttributeHaystack,
        string $superAttributeKey,
        string $superAttributeValue
    ): bool {
        return isset($superAttributeHaystack[$superAttributeKey]) && (string)$superAttributeHaystack[$superAttributeKey] === $superAttributeValue;
    }

    protected function hasAttributeWithValue(array $availableAttributes, string $attributeKey, string $attributeValue): bool
    {
        return isset($availableAttributes[$attributeKey]) && in_array($attributeValue, $availableAttributes[$attributeKey], true);
    }
}

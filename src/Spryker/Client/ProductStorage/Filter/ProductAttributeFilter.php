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

        $attributeVariantMapCompatibleWithSelection = array_filter($attributeVariantMap, function ($attributeVariantMapOption) use ($selectedAttributes) {
            return $this->isAttributeCompatibleWithSelection($attributeVariantMapOption, $selectedAttributes);
        });

        foreach ($attributeVariantMapCompatibleWithSelection as $attributeVariantMapOption) {
            $availableAttributes = $this->filterAvailableAttributes(
                $attributeVariantMapOption,
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
            if (!str_contains($attributePath, static::ATTRIBUTE_MAP_PATH_DELIMITER)) {
                continue;
            }

            [$attributeKey, $attributeValue] = explode(static::ATTRIBUTE_MAP_PATH_DELIMITER, $attributePath);
            $filteredAttributes[$attributeKey][] = $attributeValue;
        }

        return $filteredAttributes;
    }

    /**
     * @param array<string, mixed> $attributeVariantMapOption
     * @param array<string, mixed> $selectedAttributes
     * @param array<string, list<string>> $availableAttributes
     *
     * @return array<string, list<string>>
     */
    protected function filterAvailableAttributes(
        array $attributeVariantMapOption,
        array $selectedAttributes,
        array $availableAttributes
    ): array {
        $unselectedAttributes = array_diff_key($attributeVariantMapOption, $selectedAttributes);

        $selectedAttributesNotMatchingWithAttributeVariantMapOption = $this->getSelectedAttributesNotMatchingWithAttributeVariantMapOption(
            $attributeVariantMapOption,
            $selectedAttributes,
        );

        foreach ($attributeVariantMapOption as $attributeKey => $attributeValue) {
            $attributeValue = (string)$attributeValue;

            if ($selectedAttributesNotMatchingWithAttributeVariantMapOption && array_key_exists($attributeKey, $unselectedAttributes)) {
                continue;
            }

            if ($this->hasAttributeWithValue($availableAttributes, $attributeKey, $attributeValue)) {
                continue;
            }

            $availableAttributes[$attributeKey][] = $attributeValue;
        }

        return $availableAttributes;
    }

    /**
     * A variant is compatible when it differs from the selection in at most one attribute, no matter
     * how many attributes are selected: that attribute is the one the shopper would have to switch to
     * reach this variant. Differing in nothing means the variant matches the selection entirely, while
     * differing in more than one attribute puts it out of reach of a single switch.
     *
     * @param array<string, mixed> $attributeVariantMapOption
     * @param array<string, mixed> $selectedAttributes
     */
    protected function isAttributeCompatibleWithSelection(array $attributeVariantMapOption, array $selectedAttributes): bool
    {
        $notMatchingWithSelectedAttributes = $this->getSelectedAttributesNotMatchingWithAttributeVariantMapOption(
            $attributeVariantMapOption,
            $selectedAttributes,
        );

        return count($notMatchingWithSelectedAttributes) <= 1;
    }

    /**
     * Super attributes with a single value across all product variants are filtered out of the attribute
     * variant map, while they are still selected automatically for the product view. Such an attribute
     * cannot tell two variants apart, so only the attributes the variant map option carries are compared.
     *
     * @param array<string, mixed> $attributeVariantMapOption
     * @param array<string, mixed> $selectedAttributes
     *
     * @return array<string, mixed>
     */
    protected function getSelectedAttributesNotMatchingWithAttributeVariantMapOption(
        array $attributeVariantMapOption,
        array $selectedAttributes
    ): array {
        $comparableSelectedAttributes = array_intersect_key($selectedAttributes, $attributeVariantMapOption);

        return array_diff_assoc($comparableSelectedAttributes, $attributeVariantMapOption);
    }

    protected function hasAttributeWithValue(array $availableAttributes, string $attributeKey, string $attributeValue): bool
    {
        return isset($availableAttributes[$attributeKey]) && in_array($attributeValue, $availableAttributes[$attributeKey], true);
    }
}

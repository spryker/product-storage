<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\ProductStorage\Resolver;

use Spryker\Client\ProductStorage\Dependency\Client\ProductStorageToLocaleInterface;
use Spryker\Client\ProductStorage\ProductStorageConfig;
use Spryker\Client\ProductStorage\Storage\ProductAbstractStorageReaderInterface;

class SelectedProductAttributesResolver implements SelectedProductAttributesResolverInterface
{
    /**
     * Bounds one bulk storage read: keeps the number of round trips low for stores with many locales,
     * while the early break avoids loading every locale when the selection resolves sooner.
     *
     * @var positive-int
     */
    protected const int LOCALE_NAME_CHUNK_SIZE = 10;

    /**
     * @uses \Spryker\Client\ProductStorage\Storage\ProductAbstractStorageReader::KEY_ID_PRODUCT_ABSTRACT
     */
    protected const string KEY_ID_PRODUCT_ABSTRACT = 'id_product_abstract';

    /**
     * @uses \Spryker\Client\ProductStorage\Filter\ProductAbstractAttributeMapRestrictionFilter::KEY_ATTRIBUTE_VARIANT_MAP
     */
    protected const string KEY_ATTRIBUTE_VARIANT_MAP = 'attribute_variant_map';

    /**
     * @uses \Spryker\Client\ProductStorage\Filter\ProductAbstractAttributeMapRestrictionFilter::KEY_SUPER_ATTRIBUTES
     */
    protected const string KEY_SUPER_ATTRIBUTES = 'super_attributes';

    public function __construct(
        protected ProductAbstractStorageReaderInterface $productAbstractStorageReader,
        protected ProductStorageToLocaleInterface $localeClient,
    ) {
    }

    /**
     * @param array<string, mixed> $productStorageData
     * @param array<string, mixed> $selectedAttributes
     *
     * @return array<string, mixed>
     */
    public function resolveSelectedProductAttributes(array $productStorageData, array $selectedAttributes, string $localeName): array
    {
        $unresolvedSelectedAttributes = $this->getUnresolvedSelectedAttributes($productStorageData, $selectedAttributes);

        if (!$unresolvedSelectedAttributes) {
            return $selectedAttributes;
        }

        $idProductAbstract = $productStorageData[static::KEY_ID_PRODUCT_ABSTRACT] ?? null;
        $attributeVariantMap = $productStorageData[ProductStorageConfig::RESOURCE_TYPE_ATTRIBUTE_MAP][static::KEY_ATTRIBUTE_VARIANT_MAP] ?? [];

        if (!$idProductAbstract || !$attributeVariantMap) {
            return $selectedAttributes;
        }

        $foreignLocaleNameChunks = array_chunk($this->getForeignLocaleNames($localeName), static::LOCALE_NAME_CHUNK_SIZE);

        foreach ($foreignLocaleNameChunks as $foreignLocaleNames) {
            $foreignProductStorageDataCollection = $this->productAbstractStorageReader
                ->getBulkProductAbstractStorageDataByProductAbstractIdAndLocaleNames((int)$idProductAbstract, $foreignLocaleNames);

            $resolvedSelectedAttributes = $this->resolveSelectedAttributesByForeignProductStorageDataCollection(
                $foreignProductStorageDataCollection,
                $unresolvedSelectedAttributes,
                $attributeVariantMap,
            );

            $selectedAttributes = array_replace($selectedAttributes, $resolvedSelectedAttributes);
            $unresolvedSelectedAttributes = array_diff_key($unresolvedSelectedAttributes, $resolvedSelectedAttributes);

            if (!$unresolvedSelectedAttributes) {
                break;
            }
        }

        return $selectedAttributes;
    }

    /**
     * @param array<string, mixed> $productStorageData
     * @param array<string, mixed> $selectedAttributes
     *
     * @return array<string, mixed>
     */
    protected function getUnresolvedSelectedAttributes(array $productStorageData, array $selectedAttributes): array
    {
        if (!$selectedAttributes) {
            return [];
        }

        $superAttributes = $productStorageData[ProductStorageConfig::RESOURCE_TYPE_ATTRIBUTE_MAP][static::KEY_SUPER_ATTRIBUTES] ?? [];

        if (!$superAttributes) {
            return [];
        }

        $unresolvedSelectedAttributes = [];

        foreach ($selectedAttributes as $attributeKey => $selectedAttributeValue) {
            if (
                !isset($superAttributes[$attributeKey])
                || !is_scalar($selectedAttributeValue)
                || $this->hasSuperAttributeValue($superAttributes[$attributeKey], (string)$selectedAttributeValue)
            ) {
                continue;
            }

            $unresolvedSelectedAttributes[$attributeKey] = $selectedAttributeValue;
        }

        return $unresolvedSelectedAttributes;
    }

    /**
     * @param array<string, array<string, mixed>> $foreignProductStorageDataCollection
     * @param array<string, mixed> $unresolvedSelectedAttributes
     * @param array<int|string, array<string, mixed>> $attributeVariantMap
     *
     * @return array<string, string>
     */
    protected function resolveSelectedAttributesByForeignProductStorageDataCollection(
        array $foreignProductStorageDataCollection,
        array $unresolvedSelectedAttributes,
        array $attributeVariantMap
    ): array {
        $resolvedSelectedAttributes = [];

        foreach ($foreignProductStorageDataCollection as $foreignProductStorageData) {
            $resolvedSelectedAttributes += $this->resolveSelectedAttributesByForeignProductStorageData(
                $foreignProductStorageData,
                array_diff_key($unresolvedSelectedAttributes, $resolvedSelectedAttributes),
                $attributeVariantMap,
            );

            if (count($resolvedSelectedAttributes) === count($unresolvedSelectedAttributes)) {
                break;
            }
        }

        return $resolvedSelectedAttributes;
    }

    /**
     * @param array<string, mixed> $foreignProductStorageData
     * @param array<string, mixed> $unresolvedSelectedAttributes
     * @param array<int|string, array<string, mixed>> $attributeVariantMap
     *
     * @return array<string, string>
     */
    protected function resolveSelectedAttributesByForeignProductStorageData(
        array $foreignProductStorageData,
        array $unresolvedSelectedAttributes,
        array $attributeVariantMap
    ): array {
        $foreignAttributeVariantMap = $foreignProductStorageData[ProductStorageConfig::RESOURCE_TYPE_ATTRIBUTE_MAP][static::KEY_ATTRIBUTE_VARIANT_MAP] ?? [];

        if (!$foreignAttributeVariantMap) {
            return [];
        }

        $resolvedSelectedAttributes = [];

        foreach ($unresolvedSelectedAttributes as $attributeKey => $selectedAttributeValue) {
            $resolvedAttributeValue = $this->findAttributeValueForCurrentLocale(
                $attributeKey,
                (string)$selectedAttributeValue,
                $foreignAttributeVariantMap,
                $attributeVariantMap,
            );

            if ($resolvedAttributeValue === null) {
                continue;
            }

            $resolvedSelectedAttributes[$attributeKey] = $resolvedAttributeValue;
        }

        return $resolvedSelectedAttributes;
    }

    /**
     * The product concrete owning the selected value in the foreign locale carries the translated
     * value for the same attribute key in the current locale's attribute variant map.
     *
     * @param array<int|string, array<string, mixed>> $foreignAttributeVariantMap
     * @param array<int|string, array<string, mixed>> $attributeVariantMap
     */
    protected function findAttributeValueForCurrentLocale(
        string $attributeKey,
        string $selectedAttributeValue,
        array $foreignAttributeVariantMap,
        array $attributeVariantMap
    ): ?string {
        foreach ($foreignAttributeVariantMap as $idProductConcrete => $productSuperAttributes) {
            if (
                !isset($productSuperAttributes[$attributeKey])
                || !is_scalar($productSuperAttributes[$attributeKey])
                || (string)$productSuperAttributes[$attributeKey] !== $selectedAttributeValue
            ) {
                continue;
            }

            if (
                isset($attributeVariantMap[$idProductConcrete][$attributeKey])
                && is_scalar($attributeVariantMap[$idProductConcrete][$attributeKey])
            ) {
                return (string)$attributeVariantMap[$idProductConcrete][$attributeKey];
            }
        }

        return null;
    }

    /**
     * @param array<int, mixed> $superAttributeValues
     */
    protected function hasSuperAttributeValue(array $superAttributeValues, string $selectedAttributeValue): bool
    {
        return in_array($selectedAttributeValue, array_map('strval', array_filter($superAttributeValues, 'is_scalar')), true);
    }

    /**
     * @return list<string>
     */
    protected function getForeignLocaleNames(string $localeName): array
    {
        return array_values(array_diff($this->localeClient->getLocales(), [$localeName]));
    }
}

<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Client\ProductStorage\Filter;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\AttributeMapStorageTransfer;
use Generated\Shared\Transfer\ProductViewTransfer;
use Spryker\Client\ProductStorage\Dependency\Service\ProductStorageToUtilSanitizeServiceInterface;
use Spryker\Client\ProductStorage\Filter\ProductAttributeFilter;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Client
 * @group ProductStorage
 * @group Filter
 * @group ProductAttributeFilterTest
 * Add your own group annotations below this line
 */
class ProductAttributeFilterTest extends Unit
{
    protected const string ATTRIBUTE_KEY_COLOR = 'color';

    protected const string ATTRIBUTE_KEY_SIZE = 'size';

    protected const string ATTRIBUTE_KEY_MATERIAL = 'material';

    protected const string ATTRIBUTE_KEY_PACKAGING_UNIT = 'packaging_unit';

    /**
     * Product variants: red/s and blue/m. Neither red/m nor blue/s exists.
     *
     * @var array<int, array<string, string>>
     */
    protected const array ATTRIBUTE_VARIANT_MAP_DIAGONAL = [
        1 => [self::ATTRIBUTE_KEY_COLOR => 'red', self::ATTRIBUTE_KEY_SIZE => 's'],
        2 => [self::ATTRIBUTE_KEY_COLOR => 'blue', self::ATTRIBUTE_KEY_SIZE => 'm'],
    ];

    /**
     * Product variants: every color/size combination exists.
     *
     * @var array<int, array<string, string>>
     */
    protected const array ATTRIBUTE_VARIANT_MAP_COMPLETE = [
        1 => [self::ATTRIBUTE_KEY_COLOR => 'red', self::ATTRIBUTE_KEY_SIZE => 's'],
        2 => [self::ATTRIBUTE_KEY_COLOR => 'red', self::ATTRIBUTE_KEY_SIZE => 'm'],
        3 => [self::ATTRIBUTE_KEY_COLOR => 'blue', self::ATTRIBUTE_KEY_SIZE => 's'],
        4 => [self::ATTRIBUTE_KEY_COLOR => 'blue', self::ATTRIBUTE_KEY_SIZE => 'm'],
    ];

    public function testFilterAvailableProductAttributesReturnsEmptyArrayWhenNothingIsSelected(): void
    {
        // Arrange
        $productViewTransfer = $this->createProductViewTransfer(static::ATTRIBUTE_VARIANT_MAP_DIAGONAL, []);

        // Act
        $availableAttributes = $this->createProductAttributeFilter()->filterAvailableProductAttributes([], $productViewTransfer);

        // Assert
        $this->assertSame([], $availableAttributes);
    }

    public function testFilterAvailableProductAttributesNarrowsDownNotSelectedAttributeToCombinableValues(): void
    {
        // Arrange
        $productViewTransfer = $this->createProductViewTransfer(
            static::ATTRIBUTE_VARIANT_MAP_DIAGONAL,
            [static::ATTRIBUTE_KEY_COLOR => 'red'],
        );

        // Act
        $availableAttributes = $this->createProductAttributeFilter()->filterAvailableProductAttributes([], $productViewTransfer);

        // Assert
        $this->assertAvailableAttributes([
            static::ATTRIBUTE_KEY_COLOR => ['red', 'blue'],
            static::ATTRIBUTE_KEY_SIZE => ['s'],
        ], $availableAttributes);
    }

    public function testFilterAvailableProductAttributesNarrowsDownAlreadySelectedAttributeToCombinableValues(): void
    {
        // Arrange
        $productViewTransfer = $this->createProductViewTransfer(
            static::ATTRIBUTE_VARIANT_MAP_DIAGONAL,
            [static::ATTRIBUTE_KEY_COLOR => 'red', static::ATTRIBUTE_KEY_SIZE => 's'],
        );

        // Act
        $availableAttributes = $this->createProductAttributeFilter()->filterAvailableProductAttributes([], $productViewTransfer);

        // Assert
        $this->assertAvailableAttributes([
            static::ATTRIBUTE_KEY_COLOR => ['red'],
            static::ATTRIBUTE_KEY_SIZE => ['s'],
        ], $availableAttributes);
    }

    /**
     * A selection matching no product variant is not reachable through the storefront, because every
     * offered value belongs to an existing variant. It only occurs when the attributes come from an
     * outdated or manipulated link, and then no product concrete is resolved, so the shopper cannot
     * add the combination to the cart. Every value of the variants one switch away is offered in that
     * case, including the selected ones.
     *
     * @return void
     */
    public function testFilterAvailableProductAttributesOffersAllValuesOfCombinableVariantsWhenSelectedAttributesDoNotMatchAnyProductVariant(): void
    {
        // Arrange
        $productViewTransfer = $this->createProductViewTransfer(
            static::ATTRIBUTE_VARIANT_MAP_DIAGONAL,
            [static::ATTRIBUTE_KEY_COLOR => 'red', static::ATTRIBUTE_KEY_SIZE => 'm'],
        );

        // Act
        $availableAttributes = $this->createProductAttributeFilter()->filterAvailableProductAttributes([], $productViewTransfer);

        // Assert
        $this->assertAvailableAttributes([
            static::ATTRIBUTE_KEY_COLOR => ['red', 'blue'],
            static::ATTRIBUTE_KEY_SIZE => ['s', 'm'],
        ], $availableAttributes);
    }

    public function testFilterAvailableProductAttributesKeepsAllValuesAvailableWhenEveryCombinationExists(): void
    {
        // Arrange
        $productViewTransfer = $this->createProductViewTransfer(
            static::ATTRIBUTE_VARIANT_MAP_COMPLETE,
            [static::ATTRIBUTE_KEY_COLOR => 'red', static::ATTRIBUTE_KEY_SIZE => 's'],
        );

        // Act
        $availableAttributes = $this->createProductAttributeFilter()->filterAvailableProductAttributes([], $productViewTransfer);

        // Assert
        $this->assertAvailableAttributes([
            static::ATTRIBUTE_KEY_COLOR => ['red', 'blue'],
            static::ATTRIBUTE_KEY_SIZE => ['s', 'm'],
        ], $availableAttributes);
    }

    public function testFilterAvailableProductAttributesIgnoresProductVariantsDifferingInMoreThanOneAttribute(): void
    {
        // Arrange
        $attributeVariantMap = [
            1 => [
                static::ATTRIBUTE_KEY_COLOR => 'red',
                static::ATTRIBUTE_KEY_SIZE => 's',
                static::ATTRIBUTE_KEY_MATERIAL => 'wool',
            ],
            2 => [
                static::ATTRIBUTE_KEY_COLOR => 'red',
                static::ATTRIBUTE_KEY_SIZE => 'm',
                static::ATTRIBUTE_KEY_MATERIAL => 'wool',
            ],
            // Differs from the selection in both size and material, so neither of its values is combinable.
            3 => [
                static::ATTRIBUTE_KEY_COLOR => 'red',
                static::ATTRIBUTE_KEY_SIZE => 'l',
                static::ATTRIBUTE_KEY_MATERIAL => 'cotton',
            ],
            4 => [
                static::ATTRIBUTE_KEY_COLOR => 'blue',
                static::ATTRIBUTE_KEY_SIZE => 's',
                static::ATTRIBUTE_KEY_MATERIAL => 'wool',
            ],
        ];
        $productViewTransfer = $this->createProductViewTransfer($attributeVariantMap, [
            static::ATTRIBUTE_KEY_COLOR => 'red',
            static::ATTRIBUTE_KEY_SIZE => 's',
            static::ATTRIBUTE_KEY_MATERIAL => 'wool',
        ]);

        // Act
        $availableAttributes = $this->createProductAttributeFilter()->filterAvailableProductAttributes([], $productViewTransfer);

        // Assert
        $this->assertAvailableAttributes([
            static::ATTRIBUTE_KEY_COLOR => ['red', 'blue'],
            static::ATTRIBUTE_KEY_SIZE => ['s', 'm'],
            static::ATTRIBUTE_KEY_MATERIAL => ['wool'],
        ], $availableAttributes);
    }

    public function testFilterAvailableProductAttributesSkipsEmptySelectedAttributeValues(): void
    {
        // Arrange
        $productViewTransfer = $this->createProductViewTransfer(
            static::ATTRIBUTE_VARIANT_MAP_DIAGONAL,
            [static::ATTRIBUTE_KEY_COLOR => 'blue', static::ATTRIBUTE_KEY_SIZE => ''],
        );

        // Act
        $availableAttributes = $this->createProductAttributeFilter()->filterAvailableProductAttributes([], $productViewTransfer);

        // Assert
        $this->assertAvailableAttributes([
            static::ATTRIBUTE_KEY_COLOR => ['red', 'blue'],
            static::ATTRIBUTE_KEY_SIZE => ['m'],
        ], $availableAttributes);
    }

    public function testFilterAvailableProductAttributesProvidesEachValueOnceWhenAttributeValueAndSelectedAttributeAreNotTheSameType(): void
    {
        // Arrange
        $attributeVariantMap = [
            1 => [static::ATTRIBUTE_KEY_SIZE => 40, static::ATTRIBUTE_KEY_COLOR => 'red'],
            2 => [static::ATTRIBUTE_KEY_SIZE => 40, static::ATTRIBUTE_KEY_COLOR => 'blue'],
        ];
        $productViewTransfer = $this->createProductViewTransfer(
            $attributeVariantMap,
            [static::ATTRIBUTE_KEY_SIZE => '40'],
        );

        // Act
        $availableAttributes = $this->createProductAttributeFilter()->filterAvailableProductAttributes([], $productViewTransfer);

        // Assert
        $this->assertAvailableAttributes([
            static::ATTRIBUTE_KEY_SIZE => ['40'],
            static::ATTRIBUTE_KEY_COLOR => ['red', 'blue'],
        ], $availableAttributes);
    }

    public function testFilterAvailableProductAttributesFallsBackToSelectedVariantNodeWhenAttributeVariantMapIsMissing(): void
    {
        // Arrange
        $productViewTransfer = (new ProductViewTransfer())
            ->setAttributeMap(new AttributeMapStorageTransfer())
            ->setSelectedAttributes([static::ATTRIBUTE_KEY_COLOR => 'red']);
        $selectedVariantNode = ['size:s' => [], 'size:m' => []];

        // Act
        $availableAttributes = $this->createProductAttributeFilter()
            ->filterAvailableProductAttributes($selectedVariantNode, $productViewTransfer);

        // Assert
        $this->assertAvailableAttributes([static::ATTRIBUTE_KEY_SIZE => ['s', 'm']], $availableAttributes);
    }

    /**
     * Super attributes with a single value across all variants are filtered out of the attribute variant
     * map, while `ProductVariantExpander` still selects them automatically. They must not be treated as a
     * difference, otherwise every variant looks further away from the selection than it is.
     *
     * @return void
     */
    public function testFilterAvailableProductAttributesIgnoresSelectedAttributesMissingFromAttributeVariantMap(): void
    {
        // Arrange
        $attributeVariantMap = [
            1 => [static::ATTRIBUTE_KEY_PACKAGING_UNIT => 'box'],
            2 => [static::ATTRIBUTE_KEY_PACKAGING_UNIT => 'item'],
        ];
        $productViewTransfer = $this->createProductViewTransfer($attributeVariantMap, [
            static::ATTRIBUTE_KEY_MATERIAL => 'aluminium',
            static::ATTRIBUTE_KEY_PACKAGING_UNIT => 'box',
        ]);

        // Act
        $availableAttributes = $this->createProductAttributeFilter()->filterAvailableProductAttributes([], $productViewTransfer);

        // Assert
        $this->assertAvailableAttributes([
            static::ATTRIBUTE_KEY_PACKAGING_UNIT => ['box', 'item'],
        ], $availableAttributes);
    }

    /**
     * @param array<int, array<string, string|int>> $attributeVariantMap
     * @param array<string, string> $selectedAttributes
     *
     * @return \Generated\Shared\Transfer\ProductViewTransfer
     */
    protected function createProductViewTransfer(array $attributeVariantMap, array $selectedAttributes): ProductViewTransfer
    {
        $attributeMapStorageTransfer = (new AttributeMapStorageTransfer())
            ->setAttributeVariantMap($attributeVariantMap);

        return (new ProductViewTransfer())
            ->setAttributeMap($attributeMapStorageTransfer)
            ->setSelectedAttributes($selectedAttributes);
    }

    protected function createProductAttributeFilter(): ProductAttributeFilter
    {
        $utilSanitizeServiceMock = $this->createMock(ProductStorageToUtilSanitizeServiceInterface::class);
        $utilSanitizeServiceMock->method('arrayFilterRecursive')
            ->willReturnCallback(static fn (array $array): array => array_filter($array));

        return new ProductAttributeFilter($utilSanitizeServiceMock);
    }

    /**
     * @param array<string, list<string>> $expectedAvailableAttributes
     * @param array<string, list<string>> $availableAttributes
     *
     * @return void
     */
    protected function assertAvailableAttributes(array $expectedAvailableAttributes, array $availableAttributes): void
    {
        $this->assertEqualsCanonicalizing(
            array_keys($expectedAvailableAttributes),
            array_keys($availableAttributes),
            'Expected available attributes to be provided for exactly these super attributes.',
        );

        foreach ($expectedAvailableAttributes as $attributeKey => $expectedAttributeValues) {
            $this->assertEqualsCanonicalizing(
                $expectedAttributeValues,
                $availableAttributes[$attributeKey],
                sprintf('Unexpected available values for the "%s" super attribute.', $attributeKey),
            );
        }
    }
}

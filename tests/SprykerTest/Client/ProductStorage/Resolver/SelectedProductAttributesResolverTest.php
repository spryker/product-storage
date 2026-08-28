<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Client\ProductStorage\Resolver;

use Codeception\Test\Unit;
use PHPUnit\Framework\MockObject\MockObject;
use Spryker\Client\ProductStorage\Dependency\Client\ProductStorageToLocaleInterface;
use Spryker\Client\ProductStorage\Resolver\SelectedProductAttributesResolver;
use Spryker\Client\ProductStorage\Storage\ProductAbstractStorageReaderInterface;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Client
 * @group ProductStorage
 * @group Resolver
 * @group SelectedProductAttributesResolverTest
 * Add your own group annotations below this line
 */
class SelectedProductAttributesResolverTest extends Unit
{
    protected const string LOCALE_NAME_EN = 'en_US';

    protected const string LOCALE_NAME_DE = 'de_DE';

    protected const string ATTRIBUTE_KEY_COLOR = 'color';

    protected const string ATTRIBUTE_KEY_PROCESSOR_CACHE = 'processor_cache';

    protected const int ID_PRODUCT_ABSTRACT = 112;

    /**
     * Attribute maps shaped like demo product abstract SKU 112: `color` values are localized,
     * `processor_cache` values are identical in both locales.
     *
     * @var array<string, mixed>
     */
    protected const array PRODUCT_STORAGE_DATA_EN = [
        'id_product_abstract' => self::ID_PRODUCT_ABSTRACT,
        'attribute_map' => [
            'super_attributes' => [
                self::ATTRIBUTE_KEY_COLOR => ['Black', 'White'],
                self::ATTRIBUTE_KEY_PROCESSOR_CACHE => ['3 MB', '6 MB'],
            ],
            'attribute_variant_map' => [
                1 => [self::ATTRIBUTE_KEY_COLOR => 'Black', self::ATTRIBUTE_KEY_PROCESSOR_CACHE => '3 MB'],
                2 => [self::ATTRIBUTE_KEY_COLOR => 'White', self::ATTRIBUTE_KEY_PROCESSOR_CACHE => '6 MB'],
            ],
        ],
    ];

    /**
     * @var array<string, mixed>
     */
    protected const array PRODUCT_STORAGE_DATA_DE = [
        'id_product_abstract' => self::ID_PRODUCT_ABSTRACT,
        'attribute_map' => [
            'super_attributes' => [
                self::ATTRIBUTE_KEY_COLOR => ['Schwarz', 'Weiß'],
                self::ATTRIBUTE_KEY_PROCESSOR_CACHE => ['3 MB', '6 MB'],
            ],
            'attribute_variant_map' => [
                1 => [self::ATTRIBUTE_KEY_COLOR => 'Schwarz', self::ATTRIBUTE_KEY_PROCESSOR_CACHE => '3 MB'],
                2 => [self::ATTRIBUTE_KEY_COLOR => 'Weiß', self::ATTRIBUTE_KEY_PROCESSOR_CACHE => '6 MB'],
            ],
        ],
    ];

    public function testResolvesForeignLocaleAttributeValueToCurrentLocaleValue(): void
    {
        // Arrange
        $productAbstractStorageReaderMock = $this->createProductAbstractStorageReaderMock([
            static::LOCALE_NAME_EN => static::PRODUCT_STORAGE_DATA_EN,
        ]);
        $resolver = $this->createResolver($productAbstractStorageReaderMock);

        // Act
        $selectedAttributes = $resolver->resolveSelectedProductAttributes(
            static::PRODUCT_STORAGE_DATA_DE,
            [static::ATTRIBUTE_KEY_COLOR => 'Black'],
            static::LOCALE_NAME_DE,
        );

        // Assert
        $this->assertSame([static::ATTRIBUTE_KEY_COLOR => 'Schwarz'], $selectedAttributes);
    }

    public function testKeepsCurrentLocaleAttributeValuesWithoutStorageReads(): void
    {
        // Arrange
        $productAbstractStorageReaderMock = $this->createProductAbstractStorageReaderMock([], 0);
        $resolver = $this->createResolver($productAbstractStorageReaderMock);

        // Act
        $selectedAttributes = $resolver->resolveSelectedProductAttributes(
            static::PRODUCT_STORAGE_DATA_DE,
            [static::ATTRIBUTE_KEY_COLOR => 'Schwarz', static::ATTRIBUTE_KEY_PROCESSOR_CACHE => '3 MB'],
            static::LOCALE_NAME_DE,
        );

        // Assert
        $this->assertSame(
            [static::ATTRIBUTE_KEY_COLOR => 'Schwarz', static::ATTRIBUTE_KEY_PROCESSOR_CACHE => '3 MB'],
            $selectedAttributes,
        );
    }

    public function testKeepsUnknownAttributeValueUnchangedWhenNoLocaleResolvesIt(): void
    {
        // Arrange
        $productAbstractStorageReaderMock = $this->createProductAbstractStorageReaderMock([
            static::LOCALE_NAME_EN => static::PRODUCT_STORAGE_DATA_EN,
        ]);
        $resolver = $this->createResolver($productAbstractStorageReaderMock);

        // Act
        $selectedAttributes = $resolver->resolveSelectedProductAttributes(
            static::PRODUCT_STORAGE_DATA_DE,
            [static::ATTRIBUTE_KEY_COLOR => 'Purple'],
            static::LOCALE_NAME_DE,
        );

        // Assert
        $this->assertSame([static::ATTRIBUTE_KEY_COLOR => 'Purple'], $selectedAttributes);
    }

    public function testResolvesOnlyForeignLocaleValuesInPartialMultiAttributeSelection(): void
    {
        // Arrange
        $productAbstractStorageReaderMock = $this->createProductAbstractStorageReaderMock([
            static::LOCALE_NAME_EN => static::PRODUCT_STORAGE_DATA_EN,
        ]);
        $resolver = $this->createResolver($productAbstractStorageReaderMock);

        // Act
        $selectedAttributes = $resolver->resolveSelectedProductAttributes(
            static::PRODUCT_STORAGE_DATA_DE,
            [static::ATTRIBUTE_KEY_COLOR => 'Black', static::ATTRIBUTE_KEY_PROCESSOR_CACHE => '3 MB'],
            static::LOCALE_NAME_DE,
        );

        // Assert
        $this->assertSame(
            [static::ATTRIBUTE_KEY_COLOR => 'Schwarz', static::ATTRIBUTE_KEY_PROCESSOR_CACHE => '3 MB'],
            $selectedAttributes,
        );
    }

    public function testKeepsSelectionUnchangedWhenForeignLocaleDataIsMissing(): void
    {
        // Arrange
        $productAbstractStorageReaderMock = $this->createProductAbstractStorageReaderMock([
            static::LOCALE_NAME_EN => null,
        ]);
        $resolver = $this->createResolver($productAbstractStorageReaderMock);

        // Act
        $selectedAttributes = $resolver->resolveSelectedProductAttributes(
            static::PRODUCT_STORAGE_DATA_DE,
            [static::ATTRIBUTE_KEY_COLOR => 'Black'],
            static::LOCALE_NAME_DE,
        );

        // Assert
        $this->assertSame([static::ATTRIBUTE_KEY_COLOR => 'Black'], $selectedAttributes);
    }

    public function testStopsBulkStorageReadsAfterChunkThatResolvesSelection(): void
    {
        // Arrange
        $localeNames = array_merge(
            [static::LOCALE_NAME_EN],
            $this->createDummyLocaleNames(20),
            [static::LOCALE_NAME_DE],
        );
        $productAbstractStorageReaderMock = $this->createProductAbstractStorageReaderMock(
            [static::LOCALE_NAME_EN => static::PRODUCT_STORAGE_DATA_EN],
            1,
        );
        $resolver = new SelectedProductAttributesResolver(
            $productAbstractStorageReaderMock,
            $this->createLocaleClientMock($localeNames),
        );

        // Act
        $selectedAttributes = $resolver->resolveSelectedProductAttributes(
            static::PRODUCT_STORAGE_DATA_DE,
            [static::ATTRIBUTE_KEY_COLOR => 'Black'],
            static::LOCALE_NAME_DE,
        );

        // Assert
        $this->assertSame([static::ATTRIBUTE_KEY_COLOR => 'Schwarz'], $selectedAttributes);
    }

    protected function createResolver(ProductAbstractStorageReaderInterface $productAbstractStorageReader): SelectedProductAttributesResolver
    {
        return new SelectedProductAttributesResolver($productAbstractStorageReader, $this->createLocaleClientMock());
    }

    /**
     * @param array<string, array<string, mixed>|null> $productStorageDataByLocaleName
     *
     * @return \Spryker\Client\ProductStorage\Storage\ProductAbstractStorageReaderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createProductAbstractStorageReaderMock(
        array $productStorageDataByLocaleName,
        ?int $expectedBulkStorageReadCount = null
    ): ProductAbstractStorageReaderInterface|MockObject {
        $invocationRule = $expectedBulkStorageReadCount === null
            ? $this->any()
            : $this->exactly($expectedBulkStorageReadCount);

        $productAbstractStorageReaderMock = $this->getMockBuilder(ProductAbstractStorageReaderInterface::class)->getMock();
        $productAbstractStorageReaderMock
            ->expects($invocationRule)
            ->method('getBulkProductAbstractStorageDataByProductAbstractIdAndLocaleNames')
            ->willReturnCallback(function (int $idProductAbstract, array $localeNames) use ($productStorageDataByLocaleName): array {
                return array_filter(array_intersect_key($productStorageDataByLocaleName, array_flip($localeNames)));
            });

        return $productAbstractStorageReaderMock;
    }

    /**
     * @param list<string>|null $localeNames
     */
    protected function createLocaleClientMock(?array $localeNames = null): ProductStorageToLocaleInterface|MockObject
    {
        $localeClientMock = $this->getMockBuilder(ProductStorageToLocaleInterface::class)->getMock();
        $localeClientMock
            ->method('getLocales')
            ->willReturn($localeNames ?? ['en' => static::LOCALE_NAME_EN, 'de' => static::LOCALE_NAME_DE]);

        return $localeClientMock;
    }

    /**
     * @return list<string>
     */
    protected function createDummyLocaleNames(int $localeNameCount): array
    {
        $localeNames = [];
        for ($i = 1; $i <= $localeNameCount; $i++) {
            $localeNames[] = sprintf('xx_%02d', $i);
        }

        return $localeNames;
    }
}

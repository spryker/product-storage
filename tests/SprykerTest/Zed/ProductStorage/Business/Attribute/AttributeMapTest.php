<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\ProductStorage\Business\Attribute;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\AttributeMapStorageTransfer;
use Orm\Zed\Product\Persistence\SpyProductAttributeKeyQuery;
use Orm\Zed\Product\Persistence\SpyProductQuery;
use Propel\Runtime\Collection\ObjectCollection;
use ReflectionProperty;
use Spryker\Zed\ProductStorage\Business\Attribute\AttributeMap;
use Spryker\Zed\ProductStorage\Business\Filter\SingleValueSuperAttributeFilter;
use Spryker\Zed\ProductStorage\Dependency\Facade\ProductStorageToProductInterface;
use Spryker\Zed\ProductStorage\Persistence\ProductStorageQueryContainerInterface;
use Spryker\Zed\ProductStorage\ProductStorageConfig;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group ProductStorage
 * @group Business
 * @group Attribute
 * @group AttributeMapTest
 * Add your own group annotations below this line
 */
class AttributeMapTest extends Unit
{
    /**
     * @var string
     */
    protected const FAKE_SKU_1 = 'fake-sku-1';

    /**
     * @var string
     */
    protected const FAKE_SKU_2 = 'fake-sku-2';

    /**
     * @var array
     */
    protected const FAKE_PRODUCT_ATTRIBUTES_1 = [
        'attribute_1' => 'value_1_1',
        'attribute_2' => 'value_1_2',
    ];

    /**
     * @var array
     */
    protected const FAKE_PRODUCT_ATTRIBUTES_2 = [
        'attribute_1' => 'value_2_1',
        'attribute_2' => 'value_2_2',
    ];

    /**
     * @var array
     */
    protected const FAKE_SUPER_ATTRIBUTES = [
        'attribute_1', 'attribute_2', 'attribute_3', 'attribute_4', 'attribute_5', 'attribute_6',
    ];

    /**
     * @var string
     */
    protected const KEY_ID_PRODUCT = 'spy_product.id_product';

    /**
     * @var string
     */
    protected const KEY_ATTRIBUTES = 'spy_product.attributes';

    /**
     * @var string
     */
    protected const KEY_SKU = 'spy_product.sku';

    /**
     * @var string
     */
    protected const KEY_FK_PRODUCT_ABSTRACT = 'spy_product.fk_product_abstract';

    /**
     * @var string
     */
    protected const KEY_FK_LOCALE = 'fk_locale';

    /**
     * @var string
     */
    protected const KEY_LOCALIZED_ATTRIBUTES = 'localized_attributes';

    /**
     * @var \SprykerTest\Zed\ProductStorage\ProductStorageBusinessTester
     */
    protected $tester;

    /**
     * @dataProvider generateAttributeMapBulkDataProvider
     *
     * @param array $productConcreteData1
     * @param array $productConcreteData2
     * @param bool $isProductAttributesWithSingleValueIncluded
     * @param array $expectedAttributeVariantMap
     * @param array $expectedAttributeVariants
     *
     * @return void
     */
    public function testGenerateAttributeMapBulk(
        array $productConcreteData1,
        array $productConcreteData2,
        bool $isProductAttributesWithSingleValueIncluded,
        array $expectedAttributeVariantMap,
        array $expectedAttributeVariants
    ): void {
        // Arrange
        $this->resetSuperAttributesCache();

        $productConcreteDataList = [$productConcreteData1, $productConcreteData2];
        $productStorageQueryContainerMock = $this->createProductStorageQueryContainerMock(
            $productConcreteDataList,
            static::FAKE_SUPER_ATTRIBUTES,
        );

        $productConcrete1AttributePermutations = $this->generateProductAttributePermutations(
            json_decode($productConcreteData1[static::KEY_ATTRIBUTES], true),
            $productConcreteData1[static::KEY_ID_PRODUCT],
        );
        $productConcrete2AttributePermutations = $this->generateProductAttributePermutations(
            json_decode($productConcreteData2[static::KEY_ATTRIBUTES], true),
            $productConcreteData2[static::KEY_ID_PRODUCT],
        );

        $productFacadeMock = $this->createProductFacadeMock(
            [static::FAKE_PRODUCT_ATTRIBUTES_1, static::FAKE_PRODUCT_ATTRIBUTES_2],
            [$productConcrete1AttributePermutations, $productConcrete2AttributePermutations],
        );

        $productStorageConfigMock = $this->createProductStorageConfigMock($isProductAttributesWithSingleValueIncluded);

        $attributeMap = new AttributeMap(
            $productFacadeMock,
            $productStorageQueryContainerMock,
            $productStorageConfigMock,
            new SingleValueSuperAttributeFilter(),
        );

        // Act
        $attributeMapBulk = $attributeMap->generateAttributeMapBulk([1], [64]);

        // Assert
        $this->assertCount(1, $attributeMapBulk);
        $this->assertArrayHasKey('1_64', $attributeMapBulk);
        /** @var \Generated\Shared\Transfer\AttributeMapStorageTransfer $attributeMapStorageTransfer */
        $attributeMapStorageTransfer = $attributeMapBulk['1_64'];
        $this->assertInstanceOf(AttributeMapStorageTransfer::class, $attributeMapStorageTransfer);

        $this->assertEqualsCanonicalizing($expectedAttributeVariantMap, $attributeMapStorageTransfer->getAttributeVariantMap());
        $this->assertEqualsCanonicalizing($expectedAttributeVariants, $attributeMapStorageTransfer->getAttributeVariants());
    }

    /**
     * @return array<array>
     */
    public function generateAttributeMapBulkDataProvider(): array
    {
        $productConcreteData1 = [
            static::KEY_ID_PRODUCT => 1,
            static::KEY_ATTRIBUTES => json_encode(static::FAKE_PRODUCT_ATTRIBUTES_1),
            static::KEY_SKU => static::FAKE_SKU_1,
            static::KEY_FK_PRODUCT_ABSTRACT => 1,
            static::KEY_LOCALIZED_ATTRIBUTES => '{}',
            static::KEY_FK_LOCALE => 64,
        ];

        $productConcreteData2 = [
            static::KEY_ID_PRODUCT => 2,
            static::KEY_ATTRIBUTES => json_encode(static::FAKE_PRODUCT_ATTRIBUTES_2),
            static::KEY_SKU => static::FAKE_SKU_2,
            static::KEY_FK_PRODUCT_ABSTRACT => 1,
            static::KEY_LOCALIZED_ATTRIBUTES => '{}',
            static::KEY_FK_LOCALE => 64,
        ];

        return [
            [
                $productConcreteData1,
                $productConcreteData2,
                true,
                [
                    '1' => static::FAKE_PRODUCT_ATTRIBUTES_1,
                    '2' => static::FAKE_PRODUCT_ATTRIBUTES_2,
                ],
                [],
            ],
            [
                $productConcreteData1,
                $productConcreteData2,
                false,
                [],
                [
                    'attribute_1:value_1_1' => [
                        'attribute_2:value_1_2' => [
                            'id_product_concrete' => 1,
                        ],
                    ],
                    'attribute_2:value_1_2' => [
                        'attribute_1:value_1_1' => [
                            'id_product_concrete' => 1,
                        ],
                    ],
                    'attribute_1:value_2_1' => [
                        'attribute_2:value_2_2' => [
                            'id_product_concrete' => 2,
                        ],
                    ],
                    'attribute_2:value_2_2' => [
                        'attribute_1:value_2_1' => [
                            'id_product_concrete' => 2,
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param array $productAttributesCombined
     * @param array $productAttributePermutationsCombined
     *
     * @return \Spryker\Zed\ProductStorage\Dependency\Facade\ProductStorageToProductInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createProductFacadeMock(
        array $productAttributesCombined = [],
        array $productAttributePermutationsCombined = []
    ): ProductStorageToProductInterface {
        $productFacadeMock = $this->getMockBuilder(ProductStorageToProductInterface::class)->getMock();
        $productFacadeMock->method('decodeProductAttributes')->willReturn([]);
        $productFacadeMock->method('combineRawProductAttributes')->willReturnOnConsecutiveCalls(...$productAttributesCombined);
        $productFacadeMock->method('generateAttributePermutations')->willReturnOnConsecutiveCalls(...$productAttributePermutationsCombined);

        return $productFacadeMock;
    }

    /**
     * @param array $productConcreteEntitiesData
     * @param array $superAttributesData
     *
     * @return \Spryker\Zed\ProductStorage\Persistence\ProductStorageQueryContainerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createProductStorageQueryContainerMock(
        array $productConcreteEntitiesData,
        array $superAttributesData
    ): ProductStorageQueryContainerInterface {
        $productStorageQueryContainerMock = $this->getMockBuilder(ProductStorageQueryContainerInterface::class)->getMock();
        $productStorageQueryContainerMock
            ->method('queryConcreteProductBulk')
            ->willReturn($this->createSpyProductQueryMock($productConcreteEntitiesData));
        $productStorageQueryContainerMock
            ->method('queryProductAttributeKey')
            ->willReturn($this->createSpyProductAttributeKeyQueryMock($superAttributesData));

        return $productStorageQueryContainerMock;
    }

    /**
     * @param array $productConcreteEntitiesData
     *
     * @return \Orm\Zed\Product\Persistence\SpyProductQuery|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createSpyProductQueryMock(array $productConcreteEntitiesData): SpyProductQuery
    {
        $spyProductQueryMock = $this->getMockBuilder(SpyProductQuery::class)->getMock();
        $spyProductQueryMock
            ->method('find')
            ->willReturn($this->createObjectCollectionMock($productConcreteEntitiesData));

        return $spyProductQueryMock;
    }

    /**
     * @param array $superAttributesData
     *
     * @return \Orm\Zed\Product\Persistence\SpyProductAttributeKeyQuery
     */
    protected function createSpyProductAttributeKeyQueryMock(array $superAttributesData): SpyProductAttributeKeyQuery
    {
        $spyProductAttributeKeyQueryMock = $this->getMockBuilder(SpyProductAttributeKeyQuery::class)->getMock();
        $spyProductAttributeKeyQueryMock->method('select')->willReturnSelf();
        $spyProductAttributeKeyQueryMock
            ->method('find')
            ->willReturn($this->createObjectCollectionMock($superAttributesData));

        return $spyProductAttributeKeyQueryMock;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return \Propel\Runtime\Collection\ObjectCollection|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createObjectCollectionMock(array $data): ObjectCollection
    {
        $objectCollectionMock = $this->getMockBuilder(ObjectCollection::class)->getMock();
        $objectCollectionMock->method('toArray')->willReturn($data);

        return $objectCollectionMock;
    }

    /**
     * @param bool $isOptimizedAttributeVariantsMapEnabled
     * @param bool $isProductAttributesWithSingleValueIncluded
     *
     * @return \Spryker\Zed\ProductStorage\ProductStorageConfig|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createProductStorageConfigMock(
        bool $isOptimizedAttributeVariantsMapEnabled,
        bool $isProductAttributesWithSingleValueIncluded = true
    ): ProductStorageConfig {
        $productStorageConfigMock = $this->getMockBuilder(ProductStorageConfig::class)->getMock();
        $productStorageConfigMock
            ->method('isProductAttributesWithSingleValueIncluded')
            ->willReturn($isProductAttributesWithSingleValueIncluded);
        $productStorageConfigMock
            ->method('isOptimizedAttributeVariantsMapEnabled')
            ->willReturn($isOptimizedAttributeVariantsMapEnabled);

        return $productStorageConfigMock;
    }

    /**
     * @param array<string, string> $productAttributes
     * @param int $idProduct
     *
     * @return array
     */
    protected function generateProductAttributePermutations(array $productAttributes, int $idProduct): array
    {
        return $this->tester->getLocator()->product()->facade()->generateAttributePermutations($productAttributes, $idProduct);
    }

    /**
     * @return void
     */
    protected function resetSuperAttributesCache(): void
    {
        $reflection = new ReflectionProperty(AttributeMap::class, 'superAttributesCache');
        $reflection->setAccessible(true);
        $reflection->setValue(null, null);
    }
}

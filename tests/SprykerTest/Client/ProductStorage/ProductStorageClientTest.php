<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Client\ProductStorage;

use Codeception\Test\Unit;
use Generated\Shared\DataBuilder\AttributeMapStorageBuilder;
use Generated\Shared\DataBuilder\ProductAbstractStorageBuilder;
use Generated\Shared\DataBuilder\ProductConcreteStorageBuilder;
use Generated\Shared\Transfer\AttributeMapStorageTransfer;
use Generated\Shared\Transfer\ProductAbstractStorageTransfer;
use Generated\Shared\Transfer\ProductConcreteStorageTransfer;
use Generated\Shared\Transfer\ProductViewTransfer;
use ReflectionProperty;
use Spryker\Client\ProductStorage\Dependency\Client\ProductStorageToStorageClientInterface;
use Spryker\Client\ProductStorage\Dependency\Service\ProductStorageToUtilSanitizeServiceInterface;
use Spryker\Client\ProductStorage\Filter\ProductAttributeFilter;
use Spryker\Client\ProductStorage\Filter\ProductAttributeFilterInterface;
use Spryker\Client\ProductStorage\Mapper\ProductVariantExpander;
use Spryker\Client\ProductStorage\ProductStorageDependencyProvider;
use Spryker\Client\ProductStorage\ProductStorageFactory;
use Spryker\Client\ProductStorage\Storage\ProductConcreteStorageReader;
use Spryker\Client\ProductStorage\Storage\ProductConcreteStorageReaderInterface;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Client
 * @group ProductStorage
 * @group ProductStorageClientTest
 * Add your own group annotations below this line
 */
class ProductStorageClientTest extends Unit
{
    /**
     * @var string
     */
    protected const SUPER_ATTRIBUTE_NAME_1 = 'super_attribute_name_1';

    /**
     * @var string
     */
    protected const SUPER_ATTRIBUTE_NAME_2 = 'super_attribute_name_2';

    /**
     * @var string
     */
    protected const NUMERIC_SUPER_ATTRIBUTE_NAME = 'numeric_super_attribute_name';

    /**
     * @var string
     */
    protected const SUPER_ATTRIBUTE_VALUE_1 = 'super_attribute_value_1';

    /**
     * @var string
     */
    protected const SUPER_ATTRIBUTE_VALUE_2_1 = 'super_attribute_value_2_1';

    /**
     * @var string
     */
    protected const SUPER_ATTRIBUTE_VALUE_2_2 = 'super_attribute_value_2_2';

    /**
     * @var int
     */
    protected const NUMERIC_SUPER_ATTRIBUTE_VALUE = 100;

    /**
     * @var int
     */
    protected const PRODUCT_CONCRETE_ID_1 = 10001;

    /**
     * @var int
     */
    protected const PRODUCT_CONCRETE_ID_2 = 10002;

    /**
     * @var string
     */
    protected const LOCALE_NAME = 'DE';

    /**
     * @var \SprykerTest\Client\ProductStorage\ProductStorageClientTester
     */
    protected $tester;

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->resetProductConcreteStorageReaderCache();
    }

    /**
     * @return void
     */
    public function testGetBulkProductAbstractStorageDataByProductAbstractIdsAndLocaleNameReturnsCorrectData(): void
    {
        // Arrange
        $productAbstractStorageTransfer = $this->getProductAbstractStorage();
        $idProductAbstract = $productAbstractStorageTransfer->getIdProductAbstract();
        $storeName = 'DE';

        $this->getStorageClientMock()
            ->expects($this->once())
            ->method('getMulti')
            ->willReturn([
                json_encode($productAbstractStorageTransfer->toArray()),
            ]);

        // Act
        $productAbstractStorageData = $this->tester
            ->getProductStorageClient()
            ->getBulkProductAbstractStorageDataByProductAbstractIdsForLocaleNameAndStore(
                [$idProductAbstract],
                static::LOCALE_NAME,
                $storeName,
            );

        // Assert
        $this->assertCount(1, $productAbstractStorageData);
        $this->assertArrayHasKey($idProductAbstract, $productAbstractStorageData);
        $this->assertSame($productAbstractStorageTransfer->toArray(), $productAbstractStorageData[$idProductAbstract]);
    }

    /**
     * @return void
     */
    public function testExpandProductVariantDataMergeAbstractAndConcreteArrayFilterDoesNotRemoveFalse(): void
    {
        // Arrange
        $productViewTransfer = $this->tester->createProductViewTransfer();
        $productConcreteStorageReaderMock = $this->getProductConcreteStorageReaderMock();
        $productAttributeFilterMock = $this->getProductAttributeFilterMock();

        // Act
        $productConcreteStorageData = (new ProductVariantExpander($productConcreteStorageReaderMock, $productAttributeFilterMock))
            ->expandProductVariantData($productViewTransfer, static::LOCALE_NAME);

        // Assert
        $this->assertFalse($productConcreteStorageData[ProductViewTransfer::AVAILABLE]);
    }

    /**
     * @return void
     */
    public function testExpandProductViewWithProductVariantMergeAbstractAndConcreteArrayFilterDoesNotRemoveFalse(): void
    {
        // Arrange
        $productViewTransfer = $this->tester->createProductViewTransfer();
        $this->getStorageClientMock()
            ->expects($this->once())
            ->method('get')
            ->willReturn([
                ProductViewTransfer::AVAILABLE => false,
            ]);

        // Act
        $expandedProductViewTransfer = $this->tester->getProductStorageClient()
            ->expandProductViewWithProductVariant($productViewTransfer, static::LOCALE_NAME);

        // Assert
        $this->assertFalse($expandedProductViewTransfer->getAvailable());
    }

    /**
     * @dataProvider superAttributesDataProvider
     *
     * @param \Generated\Shared\Transfer\AttributeMapStorageTransfer $attributeMapStorageTransfer
     * @param array<string> $originalSelectedAttributes
     * @param array<mixed> $expectedSelectedAttributes
     *
     * @return void
     */
    public function testExpandProductViewWithProductVariantSelectsProductVariantsWithSingleValue(
        AttributeMapStorageTransfer $attributeMapStorageTransfer,
        array $originalSelectedAttributes,
        array $expectedSelectedAttributes
    ): void {
        // Arrange
        $productViewTransfer = $this->tester->createProductViewTransfer();
        $productViewTransfer->setAttributeMap($attributeMapStorageTransfer);
        $productViewTransfer->setSelectedAttributes($originalSelectedAttributes);

        // Act
        $expandedProductViewTransfer = $this->tester->getProductStorageClient()
            ->expandProductViewWithProductVariant($productViewTransfer, static::LOCALE_NAME);

        // Assert
        $this->assertSame($expectedSelectedAttributes, $expandedProductViewTransfer->getSelectedAttributes());
    }

    /**
     * @return void
     */
    public function testExpandProductViewWithProductVariantReturnsUpdatedTransferWithCorrectData(): void
    {
        // Arrange
        $attributeMapStorageTransfer = $this->buildAttributeMapStorageTransfer([
            AttributeMapStorageTransfer::PRODUCT_CONCRETE_IDS => [static::PRODUCT_CONCRETE_ID_1, static::PRODUCT_CONCRETE_ID_2],
            AttributeMapStorageTransfer::SUPER_ATTRIBUTES => [
                static::SUPER_ATTRIBUTE_NAME_1 => [
                    static::SUPER_ATTRIBUTE_VALUE_1,
                ],
                static::SUPER_ATTRIBUTE_NAME_2 => [
                    static::SUPER_ATTRIBUTE_VALUE_2_1,
                ],
            ],
            AttributeMapStorageTransfer::ATTRIBUTE_VARIANTS => [
                sprintf('%s:%s', static::SUPER_ATTRIBUTE_NAME_1, static::SUPER_ATTRIBUTE_VALUE_1) => [
                    'id_product_concrete' => static::PRODUCT_CONCRETE_ID_1,
                ],
            ],
        ]);
        $productViewTransfer = $this->tester->createProductViewTransfer();
        $productViewTransfer->setAttributeMap($attributeMapStorageTransfer);

        $productConcreteStorageData = $this->buildProductConcreteStorageTransfer([
            ProductConcreteStorageTransfer::NAME => 'name',
            ProductConcreteStorageTransfer::SKU => 'sku',
            ProductConcreteStorageTransfer::URL => 'url',
            ProductConcreteStorageTransfer::DESCRIPTION => 'description',
            ProductConcreteStorageTransfer::ATTRIBUTES => [
                'attribute_name_1' => 'attribute_value_1',
                'attribute_name_2' => 'attribute_value_2',
            ],
        ])->modifiedToArray();
        $this->getStorageClientMock()
            ->expects($this->once())
            ->method('get')
            ->willReturn($productConcreteStorageData);

        // Act
        $expandedProductViewTransfer = $this->tester->getProductStorageClient()
            ->expandProductViewWithProductVariant($productViewTransfer, static::LOCALE_NAME);

        // Assert
        foreach ($productConcreteStorageData as $productConcreteStoragePropertyKey => $productConcreteStoragePropertyValue) {
            if ($expandedProductViewTransfer->offsetExists($productConcreteStoragePropertyKey)) {
                $this->assertSame(
                    $productConcreteStoragePropertyValue,
                    $expandedProductViewTransfer->offsetGet($productConcreteStoragePropertyKey),
                );
            }
        }
    }

    /**
     * @return void
     */
    public function testExpandProductViewWithProductVariantFilterSelectedAttributesByAttributeVariantMap(): void
    {
        // Arrange
        $attributeMapStorageTransfer = $this->buildAttributeMapStorageTransfer([
            AttributeMapStorageTransfer::PRODUCT_CONCRETE_IDS => [static::PRODUCT_CONCRETE_ID_1, static::PRODUCT_CONCRETE_ID_2],
            AttributeMapStorageTransfer::SUPER_ATTRIBUTES => [
                static::SUPER_ATTRIBUTE_NAME_1 => [
                    static::SUPER_ATTRIBUTE_VALUE_1,
                ],
                static::SUPER_ATTRIBUTE_NAME_2 => [
                    static::SUPER_ATTRIBUTE_VALUE_2_1,
                    static::SUPER_ATTRIBUTE_VALUE_2_2,
                ],
            ],
            AttributeMapStorageTransfer::ATTRIBUTE_VARIANT_MAP => [
                static::PRODUCT_CONCRETE_ID_1 => [
                    static::SUPER_ATTRIBUTE_NAME_1 => static::SUPER_ATTRIBUTE_VALUE_1,
                    static::SUPER_ATTRIBUTE_NAME_2 => static::SUPER_ATTRIBUTE_VALUE_2_1,
                ],
                static::PRODUCT_CONCRETE_ID_2 => [
                    static::SUPER_ATTRIBUTE_NAME_1 => static::SUPER_ATTRIBUTE_VALUE_1,
                    static::SUPER_ATTRIBUTE_NAME_2 => static::SUPER_ATTRIBUTE_VALUE_2_2,
                ],
            ],
        ]);

        $productViewTransfer = $this->tester->createProductViewTransfer();
        $productViewTransfer->setAttributeMap($attributeMapStorageTransfer)
            ->setSelectedAttributes([static::SUPER_ATTRIBUTE_NAME_1 => static::SUPER_ATTRIBUTE_VALUE_1]);

        // Act
        $expandedProductViewTransfer = $this->tester->getProductStorageClient()
            ->expandProductViewWithProductVariant($productViewTransfer, static::LOCALE_NAME);

        // Assert
        $this->assertSame(
            [
            static::SUPER_ATTRIBUTE_NAME_2 => [
                static::SUPER_ATTRIBUTE_VALUE_2_1,
                static::SUPER_ATTRIBUTE_VALUE_2_2,
            ]],
            $expandedProductViewTransfer->getAvailableAttributes(),
        );
    }

    /**
     * @return void
     */
    public function testExpandProductViewWithProductVariantSetsAvailableAttributesByAttributeVariantMapInCaseAttributeValueAndSelectedAttributeAreNotTheSameType(): void
    {
        // Arrange
        $attributeMapStorageTransfer = $this->buildAttributeMapStorageTransfer([
            AttributeMapStorageTransfer::PRODUCT_CONCRETE_IDS => [static::PRODUCT_CONCRETE_ID_1, static::PRODUCT_CONCRETE_ID_2],
            AttributeMapStorageTransfer::SUPER_ATTRIBUTES => [
                static::NUMERIC_SUPER_ATTRIBUTE_NAME => [
                    static::NUMERIC_SUPER_ATTRIBUTE_VALUE,
                ],
                static::SUPER_ATTRIBUTE_NAME_2 => [
                    static::SUPER_ATTRIBUTE_VALUE_2_1,
                    static::SUPER_ATTRIBUTE_VALUE_2_2,
                ],
            ],
            AttributeMapStorageTransfer::ATTRIBUTE_VARIANT_MAP => [
                static::PRODUCT_CONCRETE_ID_1 => [
                    static::NUMERIC_SUPER_ATTRIBUTE_NAME => static::NUMERIC_SUPER_ATTRIBUTE_VALUE,
                    static::SUPER_ATTRIBUTE_NAME_2 => static::SUPER_ATTRIBUTE_VALUE_2_1,
                ],
                static::PRODUCT_CONCRETE_ID_2 => [
                    static::NUMERIC_SUPER_ATTRIBUTE_NAME => static::NUMERIC_SUPER_ATTRIBUTE_VALUE,
                    static::SUPER_ATTRIBUTE_NAME_2 => static::SUPER_ATTRIBUTE_VALUE_2_2,
                ],
            ],
        ]);

        $productViewTransfer = $this->tester->createProductViewTransfer();
        $productViewTransfer->setAttributeMap($attributeMapStorageTransfer)
            ->setSelectedAttributes([static::NUMERIC_SUPER_ATTRIBUTE_NAME => (string)static::NUMERIC_SUPER_ATTRIBUTE_VALUE]);

        // Act
        $expandedProductViewTransfer = $this->tester->getProductStorageClient()
            ->expandProductViewWithProductVariant($productViewTransfer, static::LOCALE_NAME);

        // Assert
        $this->assertSame(
            [
                static::SUPER_ATTRIBUTE_NAME_2 => [
                    static::SUPER_ATTRIBUTE_VALUE_2_1,
                    static::SUPER_ATTRIBUTE_VALUE_2_2,
                ]],
            $expandedProductViewTransfer->getAvailableAttributes(),
        );
    }

    /**
     * @return void
     */
    protected function resetProductConcreteStorageReaderCache(): void
    {
        $reflection = new ReflectionProperty(ProductConcreteStorageReader::class, 'productsConcreteDataCache');
        $reflection->setAccessible(true);
        $reflection->setValue(null, []);
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Spryker\Client\ProductStorage\Dependency\Client\ProductStorageToStorageClientInterface
     */
    protected function getStorageClientMock(): ProductStorageToStorageClientInterface
    {
        $storageClientMock = $this->getMockBuilder(ProductStorageToStorageClientInterface::class)->getMock();
        $this->tester->setDependency(
            ProductStorageDependencyProvider::CLIENT_STORAGE,
            $storageClientMock,
            ProductStorageFactory::class,
        );

        return $storageClientMock;
    }

    /**
     * @return \Spryker\Client\ProductStorage\Filter\ProductAttributeFilterInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getProductAttributeFilterMock(): ProductAttributeFilterInterface
    {
        return $this->getMockBuilder(ProductAttributeFilter::class)
            ->setConstructorArgs([
                $this->getSanitizeServiceMock(),
            ])
            ->getMock();
    }

    /**
     * @return \Spryker\Client\ProductStorage\Dependency\Service\ProductStorageToUtilSanitizeServiceInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getSanitizeServiceMock(): ProductStorageToUtilSanitizeServiceInterface
    {
        return $this->getMockBuilder(ProductStorageToUtilSanitizeServiceInterface::class)->getMock();
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Spryker\Client\ProductStorage\Storage\ProductConcreteStorageReaderInterface
     */
    protected function getProductConcreteStorageReaderMock(): ProductConcreteStorageReaderInterface
    {
        $productConcreteStorageReaderMock = $this->getMockBuilder(ProductConcreteStorageReaderInterface::class)->getMock();
        $productConcreteStorageReaderMock
            ->method('findProductConcreteStorageData')
            ->willReturn([ProductViewTransfer::AVAILABLE => false]);

        return $productConcreteStorageReaderMock;
    }

    /**
     * @param array $seedData
     *
     * @return \Generated\Shared\Transfer\ProductAbstractStorageTransfer
     */
    protected function getProductAbstractStorage(array $seedData = []): ProductAbstractStorageTransfer
    {
        return (new ProductAbstractStorageBuilder($seedData))->build();
    }

    /**
     * @param array<mixed> $seedData
     *
     * @return \Generated\Shared\Transfer\AttributeMapStorageTransfer
     */
    protected function buildAttributeMapStorageTransfer(array $seedData): AttributeMapStorageTransfer
    {
        return (new AttributeMapStorageBuilder($seedData))->build();
    }

    /**
     * @param array<mixed> $seedData
     *
     * @return \Generated\Shared\Transfer\ProductConcreteStorageTransfer
     */
    protected function buildProductConcreteStorageTransfer(array $seedData): ProductConcreteStorageTransfer
    {
        return (new ProductConcreteStorageBuilder($seedData))->build();
    }

    /**
     * @return array<array<mixed>>
     */
    public function superAttributesDataProvider(): array
    {
        return [
            'super attributes with single and multiple values and existing selected attribute' => [
                'attributeMapStorageTransfer' => $this->buildAttributeMapStorageTransfer([
                    AttributeMapStorageTransfer::SUPER_ATTRIBUTES => [
                        static::SUPER_ATTRIBUTE_NAME_1 => [
                            static::SUPER_ATTRIBUTE_VALUE_1,
                        ],
                        static::SUPER_ATTRIBUTE_NAME_2 => [
                            static::SUPER_ATTRIBUTE_VALUE_2_1,
                            static::SUPER_ATTRIBUTE_VALUE_2_2,
                        ],
                    ],
                ]),
                'originalSelectedAttributes' => [
                    static::SUPER_ATTRIBUTE_NAME_2 => static::SUPER_ATTRIBUTE_VALUE_2_1,
                ],
                'expectedSelectedAttributes' => [
                    static::SUPER_ATTRIBUTE_NAME_1 => static::SUPER_ATTRIBUTE_VALUE_1,
                    static::SUPER_ATTRIBUTE_NAME_2 => static::SUPER_ATTRIBUTE_VALUE_2_1,
                ],
            ],
            'super attributes with single and multiple values without existing selected attribute' => [
                'attributeMapStorageTransfer' => $this->buildAttributeMapStorageTransfer([
                    AttributeMapStorageTransfer::SUPER_ATTRIBUTES => [
                        static::SUPER_ATTRIBUTE_NAME_1 => [
                            static::SUPER_ATTRIBUTE_VALUE_1,
                        ],
                        static::SUPER_ATTRIBUTE_NAME_2 => [
                            static::SUPER_ATTRIBUTE_VALUE_2_1,
                            static::SUPER_ATTRIBUTE_VALUE_2_2,
                        ],
                    ],
                ]),
                'originalSelectedAttributes' => [],
                'expectedSelectedAttributes' => [
                    static::SUPER_ATTRIBUTE_NAME_1 => static::SUPER_ATTRIBUTE_VALUE_1,
                ],
            ],
            'super attributes with only single value' => [
                'attributeMapStorageTransfer' => $this->buildAttributeMapStorageTransfer([
                    AttributeMapStorageTransfer::SUPER_ATTRIBUTES => [
                        static::SUPER_ATTRIBUTE_NAME_1 => [
                            static::SUPER_ATTRIBUTE_VALUE_1,
                        ],
                    ],
                ]),
                'originalSelectedAttributes' => [],
                'expectedSelectedAttributes' => [
                    static::SUPER_ATTRIBUTE_NAME_1 => static::SUPER_ATTRIBUTE_VALUE_1,
                ],
            ],
            'super attributes with only multiple values' => [
                'attributeMapStorageTransfer' => $this->buildAttributeMapStorageTransfer([
                    AttributeMapStorageTransfer::SUPER_ATTRIBUTES => [
                        static::SUPER_ATTRIBUTE_NAME_2 => [
                            static::SUPER_ATTRIBUTE_VALUE_2_1,
                            static::SUPER_ATTRIBUTE_VALUE_2_2,
                        ],
                    ],
                ]),
                'originalSelectedAttributes' => [],
                'expectedSelectedAttributes' => [],
            ],
            'super attributes are empty' => [
                'attributeMapStorageTransfer' => $this->buildAttributeMapStorageTransfer([]),
                'originalSelectedAttributes' => [],
                'expectedSelectedAttributes' => [],
            ],
        ];
    }
}

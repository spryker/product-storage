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
use Spryker\Client\ProductStorage\Dependency\Client\ProductStorageToLocaleInterface;
use Spryker\Client\ProductStorage\Dependency\Client\ProductStorageToStorageClientInterface;
use Spryker\Client\ProductStorage\Dependency\Service\ProductStorageToUtilSanitizeServiceInterface;
use Spryker\Client\ProductStorage\Filter\ProductAttributeFilter;
use Spryker\Client\ProductStorage\Filter\ProductAttributeFilterInterface;
use Spryker\Client\ProductStorage\Finder\AbstractProductViewTransferFinder;
use Spryker\Client\ProductStorage\Finder\ProductAbstractViewTransferFinder;
use Spryker\Client\ProductStorage\Mapper\ProductStorageDataMapper;
use Spryker\Client\ProductStorage\Mapper\ProductVariantExpander;
use Spryker\Client\ProductStorage\ProductStorageDependencyProvider;
use Spryker\Client\ProductStorage\ProductStorageFactory;
use Spryker\Client\ProductStorage\Storage\ProductAbstractStorageReader;
use Spryker\Client\ProductStorage\Storage\ProductConcreteStorageReader;
use Spryker\Client\ProductStorage\Storage\ProductConcreteStorageReaderInterface;
use Spryker\Client\ProductStorageExtension\Dependency\Plugin\ProductViewExpanderPluginInterface;
use Symfony\Component\HttpFoundation\Request;

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

    protected const string NUMERIC_SUPER_ATTRIBUTE_KEY = '10';

    /**
     * @var int
     */
    protected const PRODUCT_CONCRETE_ID_1 = 10001;

    /**
     * @var int
     */
    protected const PRODUCT_CONCRETE_ID_2 = 10002;

    /**
     * @var int
     */
    protected const ID_PRODUCT = 8881;

    /**
     * @var int
     */
    protected const ID_PRODUCT_2 = 8882;

    /**
     * @var int
     */
    protected const ID_PRODUCT_3 = 8883;

    /**
     * @var int
     */
    protected const ID_PRODUCT_4 = 8884;

    /**
     * @var int
     */
    protected const ID_PRODUCT_5 = 8885;

    /**
     * @uses \Spryker\Client\ProductStorage\Generator\ProductAttributesResetUrlGenerator::REQUEST_PARAM_ATTRIBUTES
     *
     * @var string
     */
    protected const REQUEST_PARAM_ATTRIBUTES = 'attributes';

    protected const string PRODUCT_ABSTRACT_NAME = 'Abstract product name';

    protected const string PRODUCT_CONCRETE_NAME = 'Concrete product name';

    protected const string PRODUCT_ABSTRACT_SKU = 'abstract-product-sku';

    protected const string PRODUCT_CONCRETE_SKU = 'concrete-product-sku';

    protected const string KEY_PRODUCT_VIEW_WITHOUT_PRODUCT_ABSTRACT = 'product-view-without-product-abstract';

    /**
     * @uses \Spryker\Shared\Product\ProductConfig::ATTRIBUTE_KEY_BRAND
     */
    protected const string BRAND_ATTRIBUTE_KEY = 'brand';

    protected const string BRAND = 'Samsung';

    /**
     * @var \SprykerTest\Client\ProductStorage\ProductStorageClientTester
     */
    protected ProductStorageClientTester $tester;

    public function setUp(): void
    {
        parent::setUp();

        $this->resetProductConcreteStorageReaderCache();
    }

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
                ProductStorageClientTester::LOCALE_NAME,
                $storeName,
            );

        // Assert
        $this->assertCount(1, $productAbstractStorageData);
        $this->assertArrayHasKey($idProductAbstract, $productAbstractStorageData);
        $this->assertSame($productAbstractStorageTransfer->toArray(), $productAbstractStorageData[$idProductAbstract]);
    }

    public function testExpandProductVariantDataMergeAbstractAndConcreteArrayFilterDoesNotRemoveFalse(): void
    {
        // Arrange
        $productViewTransfer = $this->tester->createProductViewTransfer();
        $productConcreteStorageReaderMock = $this->getProductConcreteStorageReaderMock();
        $productAttributeFilterMock = $this->getProductAttributeFilterMock();

        // Act
        $productConcreteStorageData = (new ProductVariantExpander($productConcreteStorageReaderMock, $productAttributeFilterMock))
            ->expandProductVariantData($productViewTransfer, ProductStorageClientTester::LOCALE_NAME);

        // Assert
        $this->assertFalse($productConcreteStorageData[ProductViewTransfer::AVAILABLE]);
    }

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
            ->expandProductViewWithProductVariant($productViewTransfer, ProductStorageClientTester::LOCALE_NAME);

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
            ->expandProductViewWithProductVariant($productViewTransfer, ProductStorageClientTester::LOCALE_NAME);

        // Assert
        $this->assertSame($expectedSelectedAttributes, $expandedProductViewTransfer->getSelectedAttributes());
    }

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
            ->expandProductViewWithProductVariant($productViewTransfer, ProductStorageClientTester::LOCALE_NAME);

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
            ->expandProductViewWithProductVariant($productViewTransfer, ProductStorageClientTester::LOCALE_NAME);

        // Assert
        $this->assertSame(
            [
            static::SUPER_ATTRIBUTE_NAME_1 => [
                static::SUPER_ATTRIBUTE_VALUE_1,
            ],
            static::SUPER_ATTRIBUTE_NAME_2 => [
                static::SUPER_ATTRIBUTE_VALUE_2_1,
                static::SUPER_ATTRIBUTE_VALUE_2_2,
            ]],
            $expandedProductViewTransfer->getAvailableAttributes(),
        );
    }

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
            ->expandProductViewWithProductVariant($productViewTransfer, ProductStorageClientTester::LOCALE_NAME);

        // Assert
        $this->assertSame(
            [
                static::NUMERIC_SUPER_ATTRIBUTE_NAME => [
                    (string)static::NUMERIC_SUPER_ATTRIBUTE_VALUE,
                ],
                static::SUPER_ATTRIBUTE_NAME_2 => [
                    static::SUPER_ATTRIBUTE_VALUE_2_1,
                    static::SUPER_ATTRIBUTE_VALUE_2_2,
                ]],
            $expandedProductViewTransfer->getAvailableAttributes(),
        );
    }

    /**
     * @dataProvider getProductViewTransfersDataProvider
     *
     * @param list<int> $storageProductAbstractIds
     * @param list<int> $productAbstractIds
     * @param list<int> $secondProductAbstractIds
     *
     * @return void
     */
    public function testGetProductAbstractViewTransfers(
        array $storageProductAbstractIds,
        array $productAbstractIds,
        array $secondProductAbstractIds
    ): void {
        // Arrange
        $this->tester->createProductViewTransfersInStorage(
            $storageProductAbstractIds,
            ProductStorageClientTester::PRODUCT_ABSTRACT_RESOURCE_NAME,
        );

        // Act
        $productViewTransfers = $this->tester->getProductStorageClient()
            ->getProductAbstractViewTransfers($productAbstractIds, ProductStorageClientTester::LOCALE_NAME);
        $secondProductViewTransfers = $this->tester->getProductStorageClient()
            ->getProductAbstractViewTransfers($secondProductAbstractIds, ProductStorageClientTester::LOCALE_NAME);

        // Assert
        $this->assertProductViewTransfersOrder($productAbstractIds, $productViewTransfers, ProductStorageClientTester::PRODUCT_ABSTRACT_RESOURCE_NAME);
        $this->assertProductViewTransfersOrder($secondProductAbstractIds, $secondProductViewTransfers, ProductStorageClientTester::PRODUCT_ABSTRACT_RESOURCE_NAME);
    }

    /**
     * @dataProvider getProductViewTransfersDataProvider
     *
     * @param list<int> $storageProductConcreteIds
     * @param list<int> $productConcreteIds
     * @param list<int> $secondProductConcreteIds
     *
     * @return void
     */
    public function testGetProductConcreteViewTransfers(
        array $storageProductConcreteIds,
        array $productConcreteIds,
        array $secondProductConcreteIds
    ): void {
        // Arrange
        $this->tester->createProductViewTransfersInStorage(
            $storageProductConcreteIds,
            ProductStorageClientTester::PRODUCT_CONCRETE_RESOURCE_NAME,
        );

        // Act
        $productViewTransfers = $this->tester->getProductStorageClient()
            ->getProductConcreteViewTransfers($productConcreteIds, ProductStorageClientTester::LOCALE_NAME);
        $secondProductViewTransfers = $this->tester->getProductStorageClient()
            ->getProductConcreteViewTransfers($secondProductConcreteIds, ProductStorageClientTester::LOCALE_NAME);

        // Assert
        $this->assertProductViewTransfersOrder($productConcreteIds, $productViewTransfers, ProductStorageClientTester::PRODUCT_CONCRETE_RESOURCE_NAME);
        $this->assertProductViewTransfersOrder($secondProductConcreteIds, $secondProductViewTransfers, ProductStorageClientTester::PRODUCT_CONCRETE_RESOURCE_NAME);
    }

    public function testMapProductStorageDataResolvesSelectedAttributesGivenInAnotherLocale(): void
    {
        // Arrange
        $this->resetProductAbstractStorageReaderCache();
        $this->getLocaleClientMock();
        $this->getStorageClientMock()
            ->method('getMulti')
            ->willReturnCallback(function (array $storageKeys): array {
                $productStorageDataByStorageKey = [];
                foreach ($storageKeys as $storageKey) {
                    $productStorageDataByStorageKey[$storageKey] = json_encode($this->getProductStorageDataWithLocalizedSuperAttributes('en_US'));
                }

                return $productStorageDataByStorageKey;
            });

        // Act
        $productViewTransfer = $this->tester->getProductStorageClient()->mapProductStorageData(
            $this->getProductStorageDataWithLocalizedSuperAttributes('de_DE'),
            'de_DE',
            ['color' => 'Black'],
        );

        // Assert
        $this->assertSame(['color' => 'Schwarz'], $productViewTransfer->getSelectedAttributes());
    }

    public function testMapProductStorageDataKeepsTheAbstractProductNameWhenTheDataOfItsConcreteProductAreMergedIn(): void
    {
        // Arrange
        $this->getStorageClientMock()
            ->method('get')
            ->willReturn([
                ProductViewTransfer::ID_PRODUCT_CONCRETE => static::PRODUCT_CONCRETE_ID_1,
                ProductViewTransfer::SKU => static::PRODUCT_CONCRETE_SKU,
                ProductViewTransfer::NAME => static::PRODUCT_CONCRETE_NAME,
            ]);
        $productViewTransfer = $this->tester->getProductStorageClient()->mapProductStorageData([
            ProductViewTransfer::ID_PRODUCT_ABSTRACT => static::ID_PRODUCT,
            ProductViewTransfer::SKU => static::PRODUCT_ABSTRACT_SKU,
            ProductViewTransfer::NAME => static::PRODUCT_ABSTRACT_NAME,
            ProductViewTransfer::ATTRIBUTE_MAP => [
                AttributeMapStorageTransfer::PRODUCT_CONCRETE_IDS => [static::PRODUCT_CONCRETE_ID_1],
            ],
        ], ProductStorageClientTester::LOCALE_NAME);

        // Act
        $productViewTransfer = $this->tester->getProductStorageClient()
            ->expandProductViewWithProductVariant($productViewTransfer, ProductStorageClientTester::LOCALE_NAME);

        // Assert
        $this->assertSame(static::PRODUCT_CONCRETE_NAME, $productViewTransfer->getName());
        $this->assertSame(static::PRODUCT_ABSTRACT_NAME, $productViewTransfer->getAbstractName());
    }

    public function testMapProductStorageDataSetsTheSelectedVariantAttributesOfTheSelectedConcreteProductFromTheSuperAttributesOfItsAbstractProduct(): void
    {
        // Arrange
        $productStorageData = [
            'id_product_abstract' => static::ID_PRODUCT_2,
            'id_product_concrete' => static::PRODUCT_CONCRETE_ID_1,
            'attributes' => [static::SUPER_ATTRIBUTE_NAME_1 => static::SUPER_ATTRIBUTE_VALUE_1],
            'attribute_map' => ['super_attributes' => [static::SUPER_ATTRIBUTE_NAME_1 => [static::SUPER_ATTRIBUTE_VALUE_1]]],
        ];

        // Act
        $productViewTransfer = $this->tester->getProductStorageClient()
            ->mapProductStorageData($productStorageData, ProductStorageClientTester::LOCALE_NAME);

        // Assert
        $this->assertSame([static::SUPER_ATTRIBUTE_NAME_1 => static::SUPER_ATTRIBUTE_VALUE_1], $productViewTransfer->getSelectedVariantAttributes());
    }

    public function testMapProductStorageDataSetsTheBrandFromTheProductAttributes(): void
    {
        // Act
        $productViewTransfer = $this->tester->getProductStorageClient()->mapProductStorageData([
            ProductViewTransfer::ID_PRODUCT_ABSTRACT => static::ID_PRODUCT,
            ProductViewTransfer::ATTRIBUTES => [static::BRAND_ATTRIBUTE_KEY => static::BRAND],
        ], ProductStorageClientTester::LOCALE_NAME);

        // Assert
        $this->assertSame(static::BRAND, $productViewTransfer->getBrand());
    }

    public function testMapProductAbstractStorageDataSetsTheBrandFromTheProductAttributes(): void
    {
        // Act
        $productViewTransfer = $this->tester->getProductStorageClient()->mapProductAbstractStorageData([
            ProductViewTransfer::ID_PRODUCT_ABSTRACT => static::ID_PRODUCT,
            ProductViewTransfer::ATTRIBUTES => [static::BRAND_ATTRIBUTE_KEY => static::BRAND],
        ], ProductStorageClientTester::LOCALE_NAME);

        // Assert
        $this->assertSame(static::BRAND, $productViewTransfer->getBrand());
    }

    public function testExpandProductViewTransfersWithProductAbstractSetsTheBrandOfAProductViewBuiltByHand(): void
    {
        // Arrange
        $this->getStorageClientMock()
            ->expects($this->never())
            ->method('getMulti');
        $productViewTransfer = (new ProductViewTransfer())
            ->setAttributes([static::BRAND_ATTRIBUTE_KEY => static::BRAND]);

        // Act
        $productViewTransfers = $this->tester->getProductStorageClient()
            ->expandProductViewTransfersWithProductAbstract([$productViewTransfer], ProductStorageClientTester::LOCALE_NAME);

        // Assert
        $this->assertSame(static::BRAND, $productViewTransfers[0]->getBrand());
    }

    public function testExpandProductViewTransfersWithProductAbstractTakesTheBrandOfTheAbstractProductWhenTheProductViewHasNoAttributes(): void
    {
        // Arrange
        $this->resetProductAbstractStorageReaderCache();
        $productAbstractStorageTransfer = $this->getProductAbstractStorage([
            ProductAbstractStorageTransfer::ATTRIBUTES => [static::BRAND_ATTRIBUTE_KEY => static::BRAND],
        ]);
        $this->getStorageClientMock()->method('getMulti')->willReturn([json_encode($productAbstractStorageTransfer->toArray())]);
        $productViewTransfer = (new ProductViewTransfer())->setIdProductAbstract($productAbstractStorageTransfer->getIdProductAbstract());

        // Act
        $productViewTransfers = $this->tester->getProductStorageClient()
            ->expandProductViewTransfersWithProductAbstract([$productViewTransfer], ProductStorageClientTester::LOCALE_NAME);

        // Assert
        $this->assertSame(static::BRAND, $productViewTransfers[0]->getBrand());
    }

    public function testExpandProductViewTransfersWithProductAbstractSetsTheAbstractSkuAndNameAndKeepsTheKeys(): void
    {
        // Arrange
        $this->resetProductAbstractStorageReaderCache();
        $productAbstractStorageTransfer = $this->getProductAbstractStorage([
            ProductAbstractStorageTransfer::ID_PRODUCT_ABSTRACT => static::ID_PRODUCT,
            ProductAbstractStorageTransfer::SKU => static::PRODUCT_ABSTRACT_SKU,
            ProductAbstractStorageTransfer::NAME => static::PRODUCT_ABSTRACT_NAME,
        ]);
        $this->getStorageClientMock()
            ->expects($this->once())
            ->method('getMulti')
            ->willReturn([json_encode($productAbstractStorageTransfer->toArray())]);
        $productViewTransfers = [
            static::PRODUCT_CONCRETE_SKU => $this->tester->createProductViewTransfer([ProductViewTransfer::ID_PRODUCT_ABSTRACT => static::ID_PRODUCT]),
            static::KEY_PRODUCT_VIEW_WITHOUT_PRODUCT_ABSTRACT => new ProductViewTransfer(),
        ];

        // Act
        $productViewTransfers = $this->tester->getProductStorageClient()
            ->expandProductViewTransfersWithProductAbstract($productViewTransfers, ProductStorageClientTester::LOCALE_NAME);

        // Assert
        $this->assertSame([static::PRODUCT_CONCRETE_SKU, static::KEY_PRODUCT_VIEW_WITHOUT_PRODUCT_ABSTRACT], array_keys($productViewTransfers));
        $this->assertSame(static::PRODUCT_ABSTRACT_SKU, $productViewTransfers[static::PRODUCT_CONCRETE_SKU]->getAbstractSku());
        $this->assertSame(static::PRODUCT_ABSTRACT_NAME, $productViewTransfers[static::PRODUCT_CONCRETE_SKU]->getAbstractName());
        $this->assertNull($productViewTransfers[static::KEY_PRODUCT_VIEW_WITHOUT_PRODUCT_ABSTRACT]->getAbstractName());
    }

    public function testExpandProductViewTransfersWithProductAbstractSetsTheSelectedVariantAttributesInTheStringOrderOfTheSuperAttributeKeys(): void
    {
        // Arrange
        $this->resetProductAbstractStorageReaderCache();
        $this->getStorageClientMock()
            ->method('getMulti')
            ->willReturn([json_encode($this->getProductAbstractStorageWithSuperAttributes()->toArray())]);
        $productViewTransfer = $this->tester->createProductViewTransfer([
            ProductViewTransfer::ID_PRODUCT_ABSTRACT => static::ID_PRODUCT,
            ProductViewTransfer::ID_PRODUCT_CONCRETE => static::PRODUCT_CONCRETE_ID_1,
            ProductViewTransfer::ATTRIBUTES => $this->getConcreteAttributesWithSuperAttributes(),
        ]);

        // Act
        $productViewTransfers = $this->tester->getProductStorageClient()
            ->expandProductViewTransfersWithProductAbstract([$productViewTransfer], ProductStorageClientTester::LOCALE_NAME);

        // Assert
        $this->assertSame($this->getExpectedSelectedVariantAttributes(), $productViewTransfers[0]->getSelectedVariantAttributes());
    }

    public function testExpandProductViewTransfersWithProductAbstractSetsNoSelectedVariantAttributesWhenNoConcreteProductIsSelected(): void
    {
        // Arrange
        $this->resetProductAbstractStorageReaderCache();
        $this->getStorageClientMock()
            ->method('getMulti')
            ->willReturn([json_encode($this->getProductAbstractStorageWithSuperAttributes()->toArray())]);
        $productViewTransfer = $this->tester->createProductViewTransfer([
            ProductViewTransfer::ID_PRODUCT_ABSTRACT => static::ID_PRODUCT,
            ProductViewTransfer::ATTRIBUTES => $this->getConcreteAttributesWithSuperAttributes(),
        ]);

        // Act
        $productViewTransfers = $this->tester->getProductStorageClient()
            ->expandProductViewTransfersWithProductAbstract([$productViewTransfer], ProductStorageClientTester::LOCALE_NAME);

        // Assert
        $this->assertSame([], $productViewTransfers[0]->getSelectedVariantAttributes());
    }

    public function testExpandProductViewTransfersWithProductAbstractDoesNotReadTheStorageWhenNoProductViewHasAnAbstractProduct(): void
    {
        // Arrange
        $this->getStorageClientMock()
            ->expects($this->never())
            ->method('getMulti');

        // Act
        $productViewTransfers = $this->tester->getProductStorageClient()
            ->expandProductViewTransfersWithProductAbstract([new ProductViewTransfer()], ProductStorageClientTester::LOCALE_NAME);

        // Assert
        $this->assertNull($productViewTransfers[0]->getAbstractSku());
    }

    public function testGetProductAbstractViewTransfersSetsTheSelectedVariantAttributesOfTheSelectedConcreteProduct(): void
    {
        // Arrange
        $this->resetProductAbstractViewCaches();
        $this->getStorageClientMock()
            ->method('getMulti')
            ->willReturn([json_encode($this->getProductAbstractStorageWithSuperAttributes()->toArray())]);
        $this->tester->setDependency(
            ProductStorageDependencyProvider::PLUGIN_PRODUCT_VIEW_EXPANDERS,
            [$this->createConcreteProductSelectingProductViewExpanderPluginMock()],
            ProductStorageFactory::class,
        );

        // Act
        $productViewTransfers = $this->tester->getProductStorageClient()
            ->getProductAbstractViewTransfers([static::ID_PRODUCT], ProductStorageClientTester::LOCALE_NAME);

        // Assert
        $this->assertSame($this->getExpectedSelectedVariantAttributes(), $productViewTransfers[0]->getSelectedVariantAttributes());
    }

    public function testGetProductAbstractViewTransfersSetsNoSelectedVariantAttributesWithoutConcreteProduct(): void
    {
        // Arrange
        $this->resetProductAbstractViewCaches();
        $this->getStorageClientMock()
            ->method('getMulti')
            ->willReturn([json_encode($this->getProductAbstractStorageWithSuperAttributes()->toArray())]);

        // Act
        $productViewTransfers = $this->tester->getProductStorageClient()
            ->getProductAbstractViewTransfers([static::ID_PRODUCT], ProductStorageClientTester::LOCALE_NAME);

        // Assert
        $this->assertSame([], $productViewTransfers[0]->getSelectedVariantAttributes());
    }

    public function testGetProductConcreteViewTransfersExpandsTheProductViewsWithTheirAbstractProduct(): void
    {
        // Arrange
        $this->resetProductAbstractViewCaches();
        $this->resetProductConcreteStorageReaderCache();
        $this->getStorageClientMock()
            ->method('getMulti')
            ->willReturnCallback(function (array $storageKeys): array {
                return [json_encode($this->getConcreteOrAbstractStorageDataWithNamesByStorageKey($storageKeys[0]))];
            });

        // Act
        $productViewTransfers = $this->tester->getProductStorageClient()
            ->getProductConcreteViewTransfers([static::PRODUCT_CONCRETE_ID_1], ProductStorageClientTester::LOCALE_NAME);

        // Assert
        $this->assertSame(static::PRODUCT_ABSTRACT_NAME, $productViewTransfers[0]->getAbstractName());
        $this->assertSame($this->getExpectedSelectedVariantAttributes(), $productViewTransfers[0]->getSelectedVariantAttributes());
    }

    public function testGenerateProductAttributesResetUrlQueryParametersShouldGenerateParametersAccordingToProvidedRequestQueryData(): void
    {
        // Arrange
        $productViewTransfer1 = $this->tester->createProductViewTransfer([ProductViewTransfer::ID_PRODUCT_ABSTRACT => static::ID_PRODUCT]);
        $productViewTransfer2 = $this->tester->createProductViewTransfer([ProductViewTransfer::ID_PRODUCT_ABSTRACT => static::ID_PRODUCT_2]);
        $productViewTransfer1->setAttributeMap($this->buildAttributeMapStorageTransfer([
            AttributeMapStorageTransfer::SUPER_ATTRIBUTES => [
                static::SUPER_ATTRIBUTE_NAME_1 => [
                    static::SUPER_ATTRIBUTE_VALUE_1,
                ],
            ],
        ]));
        $productViewTransfer2->setAttributeMap($this->buildAttributeMapStorageTransfer([
            AttributeMapStorageTransfer::SUPER_ATTRIBUTES => [
                static::SUPER_ATTRIBUTE_NAME_1 => [
                    static::SUPER_ATTRIBUTE_VALUE_1,
                ],
                static::SUPER_ATTRIBUTE_NAME_2 => [
                    static::SUPER_ATTRIBUTE_VALUE_2_1,
                    static::SUPER_ATTRIBUTE_VALUE_2_2,
                ],
            ],
        ]));

        $requestQuery = [
            static::REQUEST_PARAM_ATTRIBUTES => [
                $productViewTransfer1->getIdProductAbstractOrFail() => [
                    static::SUPER_ATTRIBUTE_NAME_1 => static::SUPER_ATTRIBUTE_VALUE_1,
                ],
                $productViewTransfer2->getIdProductAbstractOrFail() => [
                    static::SUPER_ATTRIBUTE_NAME_1 => '',
                    static::SUPER_ATTRIBUTE_NAME_2 => static::SUPER_ATTRIBUTE_VALUE_2_1,
                ],
            ],
        ];
        $request = (new Request($requestQuery));

        $expectedProductAttributesResetUrlQueryParameters1 = [
            static::SUPER_ATTRIBUTE_NAME_1 => http_build_query([
                static::REQUEST_PARAM_ATTRIBUTES => [
                    $productViewTransfer2->getIdProductAbstractOrFail() => [
                        static::SUPER_ATTRIBUTE_NAME_1 => '',
                        static::SUPER_ATTRIBUTE_NAME_2 => static::SUPER_ATTRIBUTE_VALUE_2_1,
                    ],
                ],
            ]),
        ];
        $expectedProductAttributesResetUrlQueryParameters2 = [
            static::SUPER_ATTRIBUTE_NAME_1 => http_build_query([
                static::REQUEST_PARAM_ATTRIBUTES => [
                    $productViewTransfer1->getIdProductAbstractOrFail() => [
                        static::SUPER_ATTRIBUTE_NAME_1 => static::SUPER_ATTRIBUTE_VALUE_1,
                    ],
                    $productViewTransfer2->getIdProductAbstractOrFail() => [
                        static::SUPER_ATTRIBUTE_NAME_2 => static::SUPER_ATTRIBUTE_VALUE_2_1,
                    ],
                ],
            ]),
            static::SUPER_ATTRIBUTE_NAME_2 => http_build_query([
                static::REQUEST_PARAM_ATTRIBUTES => [
                    $productViewTransfer1->getIdProductAbstractOrFail() => [
                        static::SUPER_ATTRIBUTE_NAME_1 => static::SUPER_ATTRIBUTE_VALUE_1,
                    ],
                    $productViewTransfer2->getIdProductAbstractOrFail() => [
                        static::SUPER_ATTRIBUTE_NAME_1 => '',
                    ],
                ],
            ]),
        ];

        // Act
        $productAttributesResetUrlQueryParameters = $this->tester->getProductStorageClient()
            ->generateProductAttributesResetUrlQueryParameters($request, [
                $productViewTransfer1,
                $productViewTransfer2,
            ]);

        // Assert
        $this->assertCount(2, $productAttributesResetUrlQueryParameters);
        $this->assertArrayHasKey($productViewTransfer1->getIdProductAbstractOrFail(), $productAttributesResetUrlQueryParameters);
        $this->assertArrayHasKey($productViewTransfer2->getIdProductAbstractOrFail(), $productAttributesResetUrlQueryParameters);
        $this->assertSame(
            $expectedProductAttributesResetUrlQueryParameters1,
            $productAttributesResetUrlQueryParameters[$productViewTransfer1->getIdProductAbstractOrFail()],
        );
        $this->assertSame(
            $expectedProductAttributesResetUrlQueryParameters2,
            $productAttributesResetUrlQueryParameters[$productViewTransfer2->getIdProductAbstractOrFail()],
        );
    }

    protected function resetProductConcreteStorageReaderCache(): void
    {
        $reflection = new ReflectionProperty(ProductConcreteStorageReader::class, 'productsConcreteDataCache');
        $reflection->setValue(null, []);
    }

    /**
     * The super attribute keys mix a numeric key, which PHP turns into an integer, with string keys, so their order is the string order.
     */
    protected function getProductAbstractStorageWithSuperAttributes(): ProductAbstractStorageTransfer
    {
        return $this->getProductAbstractStorage([
            ProductAbstractStorageTransfer::ID_PRODUCT_ABSTRACT => static::ID_PRODUCT,
            ProductAbstractStorageTransfer::ATTRIBUTE_MAP => [
                AttributeMapStorageTransfer::SUPER_ATTRIBUTES => [
                    static::SUPER_ATTRIBUTE_NAME_2 => [static::SUPER_ATTRIBUTE_VALUE_2_1, static::SUPER_ATTRIBUTE_VALUE_2_2],
                    static::NUMERIC_SUPER_ATTRIBUTE_KEY => [static::NUMERIC_SUPER_ATTRIBUTE_VALUE],
                    static::SUPER_ATTRIBUTE_NAME_1 => [static::SUPER_ATTRIBUTE_VALUE_1],
                ],
            ],
        ]);
    }

    /**
     * @return array<int|string, mixed>
     */
    protected function getConcreteAttributesWithSuperAttributes(): array
    {
        return [
            static::SUPER_ATTRIBUTE_NAME_1 => static::SUPER_ATTRIBUTE_VALUE_1,
            static::SUPER_ATTRIBUTE_NAME_2 => static::SUPER_ATTRIBUTE_VALUE_2_2,
            static::NUMERIC_SUPER_ATTRIBUTE_KEY => static::NUMERIC_SUPER_ATTRIBUTE_VALUE,
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function getExpectedSelectedVariantAttributes(): array
    {
        return [
            static::NUMERIC_SUPER_ATTRIBUTE_KEY => (string)static::NUMERIC_SUPER_ATTRIBUTE_VALUE,
            static::SUPER_ATTRIBUTE_NAME_1 => static::SUPER_ATTRIBUTE_VALUE_1,
            static::SUPER_ATTRIBUTE_NAME_2 => static::SUPER_ATTRIBUTE_VALUE_2_2,
        ];
    }

    protected function resetProductAbstractViewCaches(): void
    {
        $this->resetProductAbstractStorageReaderCache();
        (new ReflectionProperty(AbstractProductViewTransferFinder::class, 'productViewTransfersCache'))->setValue(null, []);
        (new ReflectionProperty(ProductAbstractViewTransferFinder::class, 'productViewTransfersCache'))->setValue(null, []);
        (new ReflectionProperty(ProductStorageDataMapper::class, 'mappingCache'))->setValue(null, []);
    }

    protected function createConcreteProductSelectingProductViewExpanderPluginMock(): ProductViewExpanderPluginInterface
    {
        $productViewExpanderPluginMock = $this->createMock(ProductViewExpanderPluginInterface::class);
        $productViewExpanderPluginMock->method('expandProductViewTransfer')
            ->willReturnCallback(function (ProductViewTransfer $productViewTransfer): ProductViewTransfer {
                return $productViewTransfer
                    ->setIdProductConcrete(static::PRODUCT_CONCRETE_ID_1)
                    ->setAttributes($this->getConcreteAttributesWithSuperAttributes());
            });

        return $productViewExpanderPluginMock;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getConcreteOrAbstractStorageDataWithNamesByStorageKey(string $storageKey): array
    {
        if (str_contains($storageKey, 'product_abstract')) {
            return $this->getProductAbstractStorageWithSuperAttributes()
                ->setName(static::PRODUCT_ABSTRACT_NAME)
                ->setSku(static::PRODUCT_ABSTRACT_SKU)
                ->toArray();
        }

        return [
            'id_product_concrete' => static::PRODUCT_CONCRETE_ID_1,
            'id_product_abstract' => static::ID_PRODUCT,
            'name' => static::PRODUCT_CONCRETE_NAME,
            'sku' => static::PRODUCT_CONCRETE_SKU,
            'attributes' => $this->getConcreteAttributesWithSuperAttributes(),
        ];
    }

    protected function resetProductAbstractStorageReaderCache(): void
    {
        $reflection = new ReflectionProperty(ProductAbstractStorageReader::class, 'productsAbstractDataCache');
        $reflection->setAccessible(true);
        $reflection->setValue(null, []);
    }

    /**
     * @return array<string, mixed>
     */
    protected function getProductStorageDataWithLocalizedSuperAttributes(string $localeName): array
    {
        $colorValuesByLocaleName = [
            'en_US' => ['Black', 'White'],
            'de_DE' => ['Schwarz', 'Weiß'],
        ];
        $colorValues = $colorValuesByLocaleName[$localeName];

        return [
            'id_product_abstract' => static::ID_PRODUCT,
            'attribute_map' => [
                'super_attributes' => [
                    'color' => $colorValues,
                ],
                'attribute_variant_map' => [
                    static::PRODUCT_CONCRETE_ID_1 => ['color' => $colorValues[0]],
                    static::PRODUCT_CONCRETE_ID_2 => ['color' => $colorValues[1]],
                ],
            ],
        ];
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Spryker\Client\ProductStorage\Dependency\Client\ProductStorageToLocaleInterface
     */
    protected function getLocaleClientMock(): ProductStorageToLocaleInterface
    {
        $localeClientMock = $this->getMockBuilder(ProductStorageToLocaleInterface::class)->getMock();
        $localeClientMock->method('getCurrentLocale')->willReturn('de_DE');
        $localeClientMock->method('getLocales')->willReturn(['en' => 'en_US', 'de' => 'de_DE']);
        $this->tester->setDependency(
            ProductStorageDependencyProvider::CLIENT_LOCALE,
            $localeClientMock,
            ProductStorageFactory::class,
        );

        return $localeClientMock;
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

    /**
     * @return array<string, array<list<int>>>
     */
    protected function getProductViewTransfersDataProvider(): array
    {
        return [
            'Should return product views transfers ordered by given product ids when no items should be cached.' => [
                [static::ID_PRODUCT, static::ID_PRODUCT_2, static::ID_PRODUCT_3, static::ID_PRODUCT_4],
                [static::ID_PRODUCT_4, static::ID_PRODUCT],
                [static::ID_PRODUCT_3, static::ID_PRODUCT_2],
            ],
            'Should return product views transfers ordered by given product ids when all items should be cached.' => [
                [static::ID_PRODUCT, static::ID_PRODUCT_2, static::ID_PRODUCT_3, static::ID_PRODUCT_4],
                [static::ID_PRODUCT_4, static::ID_PRODUCT_2, static::ID_PRODUCT],
                [static::ID_PRODUCT_2, static::ID_PRODUCT_4, static::ID_PRODUCT],
            ],
            'Should return product views transfers ordered by given product ids when some of items should be cached.' => [
                [static::ID_PRODUCT, static::ID_PRODUCT_2, static::ID_PRODUCT_3, static::ID_PRODUCT_4, static::ID_PRODUCT_5],
                [static::ID_PRODUCT_4, static::ID_PRODUCT],
                [static::ID_PRODUCT, static::ID_PRODUCT_5, static::ID_PRODUCT_3, static::ID_PRODUCT_2, static::ID_PRODUCT_4],
            ],
        ];
    }

    /**
     * @param list<int> $productIds
     * @param list<\Generated\Shared\Transfer\ProductViewTransfer> $productViewTransfers
     * @param string $resourceName
     *
     * @return void
     */
    protected function assertProductViewTransfersOrder(array $productIds, array $productViewTransfers, string $resourceName): void
    {
        foreach ($productIds as $index => $expectedId) {
            $this->assertArrayHasKey($index, $productViewTransfers);

            $actualId = $resourceName === ProductStorageClientTester::PRODUCT_ABSTRACT_RESOURCE_NAME ?
                $productViewTransfers[$index]->getIdProductAbstract() : $productViewTransfers[$index]->getIdProductConcrete();

            $this->assertSame($productViewTransfers[$index]->getSku(), $productViewTransfers[$index]->getAbstractSku());
            $this->assertSame($expectedId, $actualId);
        }
    }
}

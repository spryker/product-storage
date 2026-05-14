<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Client\ProductStorage;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\StoreTransfer;
use ReflectionClass;
use Spryker\Client\ProductStorage\Dependency\Client\ProductStorageToStorageClientInterface;
use Spryker\Client\ProductStorage\Dependency\Client\ProductStorageToStoreClientInterface;
use Spryker\Client\ProductStorage\Dependency\Service\ProductStorageToSynchronizationServiceInterface;
use Spryker\Client\ProductStorage\Filter\ProductAbstractAttributeMapRestrictionFilterInterface;
use Spryker\Client\ProductStorage\ProductStorageConfig;
use Spryker\Client\ProductStorage\Storage\ProductAbstractStorageReader;
use Spryker\Service\Synchronization\Dependency\Plugin\SynchronizationKeyGeneratorPluginInterface;
use Spryker\Shared\ProductStorage\ProductStorageConfig as SharedProductStorageConfig;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Client
 * @group ProductStorage
 * @group ProductAbstractStorageReaderTest
 * Add your own group annotations below this line
 */
class ProductAbstractStorageReaderTest extends Unit
{
    protected const int PRODUCT_ABSTRACT_ID = 1;

    protected const string LOCALE_NAME = 'de_DE';

    protected const string STORE_NAME_DE = 'DE';

    protected const string STORE_NAME_AT = 'AT';

    protected const string STORAGE_KEY = 'product_abstract:unified:de_de:1';

    protected ProductStorageClientTester $tester;

    protected function setUp(): void
    {
        parent::setUp();

        $reflection = new ReflectionClass(ProductAbstractStorageReader::class);

        $reflection->getProperty('storageKeyBuilder')->setValue(null, null);
        $reflection->getProperty('storeName')->setValue(null, null);
        $reflection->getProperty('productsAbstractDataCache')->setValue(null, []);
    }

    public function testGivenUnifiedDisabledWhenGetStoreNameThenReturnsRealStoreName(): void
    {
        // Arrange
        $reader = $this->buildReader(isUnifiedEnabled: false, currentStoreName: static::STORE_NAME_DE);

        // Act
        $result = $this->callProtectedMethod($reader, 'getStoreName');

        // Assert
        $this->assertSame(static::STORE_NAME_DE, $result);
    }

    public function testGivenUnifiedEnabledWhenGetStoreNameThenReturnsUnifiedKey(): void
    {
        // Arrange
        $reader = $this->buildReader(isUnifiedEnabled: true, currentStoreName: static::STORE_NAME_DE);

        // Act
        $result = $this->callProtectedMethod($reader, 'getStoreName');

        // Assert
        $this->assertSame(SharedProductStorageConfig::PRODUCT_ABSTRACT_STORAGE_UNIFIED_STORE_KEY, $result);
    }

    public function testGivenUnifiedEnabledAndCurrentStoreInMapWhenFindStorageDataThenReturnsData(): void
    {
        // Arrange
        $productData = $this->buildProductData(storesMap: [static::STORE_NAME_DE, static::STORE_NAME_AT]);

        $storageMock = $this->createMock(ProductStorageToStorageClientInterface::class);
        $storageMock->method('get')->willReturn($productData);

        $reader = $this->buildReader(
            isUnifiedEnabled: true,
            currentStoreName: static::STORE_NAME_DE,
            storageClient: $storageMock,
        );

        // Act
        $result = $reader->findProductAbstractStorageData(static::PRODUCT_ABSTRACT_ID, static::LOCALE_NAME);

        // Assert
        $this->assertNotNull($result);
        $this->assertSame(static::PRODUCT_ABSTRACT_ID, $result['id_product_abstract']);
    }

    public function testGivenUnifiedEnabledAndCurrentStoreNotInMapWhenFindStorageDataThenReturnsNull(): void
    {
        // Arrange
        $productData = $this->buildProductData(storesMap: [static::STORE_NAME_AT]);

        $storageMock = $this->createMock(ProductStorageToStorageClientInterface::class);
        $storageMock->method('get')->willReturn($productData);

        $reader = $this->buildReader(
            isUnifiedEnabled: true,
            currentStoreName: static::STORE_NAME_DE,
            storageClient: $storageMock,
        );

        // Act
        $result = $reader->findProductAbstractStorageData(static::PRODUCT_ABSTRACT_ID, static::LOCALE_NAME);

        // Assert
        $this->assertNull($result);
    }

    public function testGivenUnifiedEnabledAndStoresMapMissingWhenFindStorageDataThenReturnsDataAsIs(): void
    {
        // Arrange
        $productData = $this->buildProductData(storesMap: null);

        $storageMock = $this->createMock(ProductStorageToStorageClientInterface::class);
        $storageMock->method('get')->willReturn($productData);

        $reader = $this->buildReader(
            isUnifiedEnabled: true,
            currentStoreName: static::STORE_NAME_DE,
            storageClient: $storageMock,
        );

        // Act
        $result = $reader->findProductAbstractStorageData(static::PRODUCT_ABSTRACT_ID, static::LOCALE_NAME);

        // Assert
        $this->assertNotNull($result);
        $this->assertSame(static::PRODUCT_ABSTRACT_ID, $result['id_product_abstract']);
    }

    /**
     * @param array<string>|null $storesMap
     *
     * @return array<string, mixed>
     */
    protected function buildProductData(?array $storesMap): array
    {
        $data = [
            'id_product_abstract' => static::PRODUCT_ABSTRACT_ID,
            'sku' => 'SKU-1',
        ];

        if ($storesMap !== null) {
            $data['product_abstract_stores_map'] = $storesMap;
        }

        return $data;
    }

    protected function buildReader(
        bool $isUnifiedEnabled,
        string $currentStoreName,
        ?ProductStorageToStorageClientInterface $storageClient = null
    ): ProductAbstractStorageReader {
        $keyBuilderMock = $this->createMock(SynchronizationKeyGeneratorPluginInterface::class);
        $keyBuilderMock->method('generateKey')->willReturn(static::STORAGE_KEY);

        $syncServiceMock = $this->createMock(ProductStorageToSynchronizationServiceInterface::class);
        $syncServiceMock->method('getStorageKeyBuilder')->willReturn($keyBuilderMock);

        $storeTransfer = (new StoreTransfer())->setName($currentStoreName);
        $storeClientMock = $this->createMock(ProductStorageToStoreClientInterface::class);
        $storeClientMock->method('getCurrentStore')->willReturn($storeTransfer);

        $filterMock = $this->createMock(ProductAbstractAttributeMapRestrictionFilterInterface::class);
        $filterMock->method('filterAbstractProductVariantsData')->willReturnArgument(0);

        $configMock = $this->createMock(ProductStorageConfig::class);
        $configMock->method('isProductAbstractStorageUnifiedEnabled')->willReturn($isUnifiedEnabled);

        return new ProductAbstractStorageReader(
            $storageClient ?? $this->createMock(ProductStorageToStorageClientInterface::class),
            $syncServiceMock,
            $storeClientMock,
            $filterMock,
            $configMock,
            [],
            [],
        );
    }

    protected function callProtectedMethod(object $object, string $method): mixed
    {
        return (new ReflectionClass($object))->getMethod($method)->invoke($object);
    }
}

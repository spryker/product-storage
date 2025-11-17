<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\ProductStorage\Communication\Plugin;

use ArrayObject;
use Codeception\Test\Unit;
use Generated\Shared\Transfer\ProductConcreteReadinessRequestTransfer;
use Generated\Shared\Transfer\ProductConcreteTransfer;
use Generated\Shared\Transfer\StoreTransfer;
use Spryker\Client\ProductStorage\ProductStorageClientInterface;
use Spryker\Zed\ProductStorage\Business\ProductStorageBusinessFactory;
use Spryker\Zed\ProductStorage\Business\Provider\StorageProductConcreteReadinessProvider;
use Spryker\Zed\ProductStorage\Communication\Plugin\ProductManagement\StorageProductConcreteReadinessProviderPlugin;
use Spryker\Zed\ProductStorage\Dependency\Facade\ProductStorageToStoreFacadeInterface;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group ProductStorage
 * @group Communication
 * @group Plugin
 * @group StorageProductConcreteReadinessProviderPluginTest
 * Add your own group annotations below this line
 */
class StorageProductConcreteReadinessProviderPluginTest extends Unit
{
    /**
     * @return void
     */
    public function testProvideFormatsLocales(): void
    {
        // Arrange
        $idProductConcrete = 123;
        $plugin = $this->createPluginWithMocks(
            $this->createProductStorageClientMockWithLocales([
                'de_DE' => true,
                'en_US' => true,
                'fr_FR' => false,
            ]),
        );

        $requestTransfer = (new ProductConcreteReadinessRequestTransfer())
            ->setProductConcrete((new ProductConcreteTransfer())->setIdProductConcrete($idProductConcrete));

        // Act
        $result = $plugin->provide($requestTransfer, new ArrayObject());

        // Assert
        $this->assertCount(1, $result);
        $productReadiness = $result[0];
        $this->assertSame('In Storage for locale', $productReadiness->getTitle());
        $this->assertSame('de_DE, en_US', $productReadiness->getValues()[0]);
    }

    /**
     * @return void
     */
    public function testProvideReturnsDashWhenNoLocalesProvided(): void
    {
        // Arrange
        $plugin = $this->createPluginWithMocks(
            $this->createProductStorageClientMockWithLocales([
                'de_DE' => false,
                'en_US' => false,
            ]),
        );

        $requestTransfer = (new ProductConcreteReadinessRequestTransfer())
            ->setProductConcrete((new ProductConcreteTransfer())->setIdProductConcrete(456));

        // Act
        $result = $plugin->provide($requestTransfer, new ArrayObject());

        // Assert
        $this->assertSame('-', $result[0]->getValues()[0]);
    }

    /**
     * @return void
     */
    public function testProvideReturnsDashWhenNoStoresProvided(): void
    {
        // Arrange
        $plugin = $this->createPluginWithMocks(
            $this->createProductStorageClientMockWithLocales([]),
        );

        $requestTransfer = (new ProductConcreteReadinessRequestTransfer())
            ->setProductConcrete((new ProductConcreteTransfer())->setIdProductConcrete(789));

        // Act
        $result = $plugin->provide($requestTransfer, new ArrayObject());

        // Assert
        $this->assertSame('-', $result[0]->getValues()[0]);
    }

    /**
     * @param \Spryker\Client\ProductStorage\ProductStorageClientInterface $productStorageClientMock
     *
     * @return \Spryker\Zed\ProductStorage\Communication\Plugin\ProductManagement\StorageProductConcreteReadinessProviderPlugin
     */
    protected function createPluginWithMocks(
        ProductStorageClientInterface $productStorageClientMock
    ): StorageProductConcreteReadinessProviderPlugin {
        $storeFacadeMock = $this->createStoreFacadeMock();

        $provider = new StorageProductConcreteReadinessProvider(
            $productStorageClientMock,
            $storeFacadeMock,
        );

        $factoryMock = $this->getMockBuilder(ProductStorageBusinessFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['createStorageProductConcreteReadinessProvider'])
            ->getMock();

        $factoryMock->method('createStorageProductConcreteReadinessProvider')->willReturn($provider);

        $plugin = new StorageProductConcreteReadinessProviderPlugin();
        $plugin->setBusinessFactory($factoryMock);

        return $plugin;
    }

    /**
     * @param array<string, bool> $localeExistenceMap
     *
     * @return \Spryker\Client\ProductStorage\ProductStorageClientInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createProductStorageClientMockWithLocales(array $localeExistenceMap): ProductStorageClientInterface
    {
        $productStorageClientMock = $this->getMockBuilder(ProductStorageClientInterface::class)
            ->getMock();

        $productStorageClientMock->method('getBulkProductConcreteStorageData')
            ->willReturnCallback(function (array $productConcreteIds, string $localeIsoCode) use ($localeExistenceMap) {
                $exists = $localeExistenceMap[$localeIsoCode] ?? false;

                return $exists ? [[$productConcreteIds[0] => ['data']]] : [];
            });

        return $productStorageClientMock;
    }

    /**
     * @return \Spryker\Zed\ProductStorage\Dependency\Facade\ProductStorageToStoreFacadeInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createStoreFacadeMock(): ProductStorageToStoreFacadeInterface
    {
        $storeFacadeMock = $this->getMockBuilder(ProductStorageToStoreFacadeInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAllStores', 'getStoreByName'])
            ->getMock();

        $stores = [
            (new StoreTransfer())->setName('DE')->setAvailableLocaleIsoCodes(['de_DE']),
            (new StoreTransfer())->setName('US')->setAvailableLocaleIsoCodes(['en_US', 'fr_FR']),
        ];

        $storeFacadeMock->method('getAllStores')->willReturn($stores);
        $storeFacadeMock->method('getStoreByName')->willReturn(new StoreTransfer());

        return $storeFacadeMock;
    }
}

<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\ProductStorage\Communication\Plugin;

use ArrayObject;
use Codeception\Test\Unit;
use Generated\Shared\Transfer\ProductAbstractReadinessRequestTransfer;
use Generated\Shared\Transfer\ProductAbstractTransfer;
use Generated\Shared\Transfer\StoreTransfer;
use Spryker\Zed\ProductStorage\Business\ProductStorageBusinessFactory;
use Spryker\Zed\ProductStorage\Business\Provider\StorageTableProductAbstractReadinessProvider;
use Spryker\Zed\ProductStorage\Communication\Plugin\ProductManagement\StorageTableProductAbstractReadinessProviderPlugin;
use Spryker\Zed\ProductStorage\Dependency\Facade\ProductStorageToStoreFacadeInterface;
use Spryker\Zed\ProductStorage\Persistence\ProductStorageRepositoryInterface;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group ProductStorage
 * @group Communication
 * @group Plugin
 * @group StorageTableProductAbstractReadinessProviderPluginTest
 * Add your own group annotations below this line
 */
class StorageTableProductAbstractReadinessProviderPluginTest extends Unit
{
    /**
     * @return void
     */
    public function testProvideFormatsStoresAndLocales(): void
    {
        // Arrange
        $idProductAbstract = 123;
        $plugin = $this->createPluginWithMocks(
            $this->createProductStorageRepositoryMockWithData(),
            $this->createStoreFacadeMockWithLocales(['DE', 'US']),
        );

        $requestTransfer = (new ProductAbstractReadinessRequestTransfer())
            ->setProductAbstract((new ProductAbstractTransfer())->setIdProductAbstract($idProductAbstract));

        // Act
        $result = $plugin->provide($requestTransfer, new ArrayObject());

        // Assert
        $this->assertCount(1, $result);
        $productReadiness = $result[0];
        $this->assertSame('In Storage table for store/locale', $productReadiness->getTitle());
        $this->assertSame('DE: de_DE, en_US | US: fr_FR', $productReadiness->getValues()[0]);
    }

    /**
     * @return void
     */
    public function testProvideReturnsDashWhenNoStoresProvided(): void
    {
        // Arrange
        $plugin = $this->createPluginWithMocks(
            $this->createProductStorageRepositoryMockWithNoData(),
            $this->createEmptyStoreFacadeMock(),
        );

        $requestTransfer = (new ProductAbstractReadinessRequestTransfer())
            ->setProductAbstract((new ProductAbstractTransfer())->setIdProductAbstract(456));

        // Act
        $result = $plugin->provide($requestTransfer, new ArrayObject());

        // Assert
        $this->assertSame('-', $result[0]->getValues()[0]);
    }

    /**
     * @return void
     */
    public function testProvideHandlesPartialStoreCoverage(): void
    {
        // Arrange
        $plugin = $this->createPluginWithMocks(
            $this->createProductStorageRepositoryMockWithOneStore(),
            $this->createStoreFacadeMockWithLocales(['DE', 'US']),
        );

        $requestTransfer = (new ProductAbstractReadinessRequestTransfer())
            ->setProductAbstract((new ProductAbstractTransfer())->setIdProductAbstract(789));

        // Act
        $result = $plugin->provide($requestTransfer, new ArrayObject());

        // Assert
        $this->assertSame('DE: de_DE | US: -', $result[0]->getValues()[0]);
    }

    /**
     * @param \Spryker\Zed\ProductStorage\Persistence\ProductStorageRepositoryInterface $repositoryMock
     * @param \Spryker\Zed\ProductStorage\Dependency\Facade\ProductStorageToStoreFacadeInterface $storeFacadeMock
     *
     * @return \Spryker\Zed\ProductStorage\Communication\Plugin\ProductManagement\StorageTableProductAbstractReadinessProviderPlugin
     */
    protected function createPluginWithMocks(
        ProductStorageRepositoryInterface $repositoryMock,
        ProductStorageToStoreFacadeInterface $storeFacadeMock
    ): StorageTableProductAbstractReadinessProviderPlugin {
        $provider = new StorageTableProductAbstractReadinessProvider($repositoryMock, $storeFacadeMock);

        $factoryMock = $this->getMockBuilder(ProductStorageBusinessFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['createStorageTableProductAbstractReadinessProvider'])
            ->getMock();

        $factoryMock->method('createStorageTableProductAbstractReadinessProvider')->willReturn($provider);

        $plugin = new StorageTableProductAbstractReadinessProviderPlugin();
        $plugin->setBusinessFactory($factoryMock);

        return $plugin;
    }

    /**
     * @return \Spryker\Zed\ProductStorage\Persistence\ProductStorageRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createProductStorageRepositoryMockWithData(): ProductStorageRepositoryInterface
    {
        $repositoryMock = $this->getMockBuilder(ProductStorageRepositoryInterface::class)
            ->getMock();

        $productStorageData = [
            [
                'Locale' => ['locale_name' => 'de_DE'],
                'SpyProductAbstract' => [
                    'SpyProductAbstractStores' => [
                        [
                            'SpyStore' => ['name' => 'DE'],
                        ],
                    ],
                ],
            ],
            [
                'Locale' => ['locale_name' => 'en_US'],
                'SpyProductAbstract' => [
                    'SpyProductAbstractStores' => [
                        [
                            'SpyStore' => ['name' => 'DE'],
                        ],
                    ],
                ],
            ],
            [
                'Locale' => ['locale_name' => 'fr_FR'],
                'SpyProductAbstract' => [
                    'SpyProductAbstractStores' => [
                        [
                            'SpyStore' => ['name' => 'US'],
                        ],
                    ],
                ],
            ],
        ];

        $repositoryMock->method('getProductAbstractsByIds')->willReturn($productStorageData);

        return $repositoryMock;
    }

    /**
     * @return \Spryker\Zed\ProductStorage\Persistence\ProductStorageRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createProductStorageRepositoryMockWithNoData(): ProductStorageRepositoryInterface
    {
        $repositoryMock = $this->getMockBuilder(ProductStorageRepositoryInterface::class)
            ->getMock();

        $repositoryMock->method('getProductAbstractsByIds')->willReturn([]);

        return $repositoryMock;
    }

    /**
     * @return \Spryker\Zed\ProductStorage\Persistence\ProductStorageRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createProductStorageRepositoryMockWithOneStore(): ProductStorageRepositoryInterface
    {
        $repositoryMock = $this->getMockBuilder(ProductStorageRepositoryInterface::class)
            ->getMock();

        $productStorageData = [
            [
                'Locale' => ['locale_name' => 'de_DE'],
                'SpyProductAbstract' => [
                    'SpyProductAbstractStores' => [
                        [
                            'SpyStore' => ['name' => 'DE'],
                        ],
                    ],
                ],
            ],
        ];

        $repositoryMock->method('getProductAbstractsByIds')->willReturn($productStorageData);

        return $repositoryMock;
    }

    /**
     * @param array<string> $storeNames
     *
     * @return \Spryker\Zed\ProductStorage\Dependency\Facade\ProductStorageToStoreFacadeInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createStoreFacadeMockWithLocales(array $storeNames): ProductStorageToStoreFacadeInterface
    {
        $storeFacadeMock = $this->getMockBuilder(ProductStorageToStoreFacadeInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAllStores', 'getStoreByName'])
            ->getMock();

        $stores = [];
        foreach ($storeNames as $storeName) {
            $store = (new StoreTransfer())->setName($storeName);
            $stores[] = $store;
            $storeFacadeMock->method('getStoreByName')->with($storeName)->willReturn($store);
        }

        $storeFacadeMock->method('getAllStores')->willReturn($stores);

        return $storeFacadeMock;
    }

    /**
     * @return \Spryker\Zed\ProductStorage\Dependency\Facade\ProductStorageToStoreFacadeInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createEmptyStoreFacadeMock(): ProductStorageToStoreFacadeInterface
    {
        $storeFacadeMock = $this->getMockBuilder(ProductStorageToStoreFacadeInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAllStores', 'getStoreByName'])
            ->getMock();

        $storeFacadeMock->method('getAllStores')->willReturn([]);
        $storeFacadeMock->method('getStoreByName')->willReturn(new StoreTransfer());

        return $storeFacadeMock;
    }
}

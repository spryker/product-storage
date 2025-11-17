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
use Spryker\Client\ProductStorage\ProductStorageClientInterface;
use Spryker\Zed\ProductStorage\Business\ProductStorageBusinessFactory;
use Spryker\Zed\ProductStorage\Business\Provider\StorageProductAbstractReadinessProvider;
use Spryker\Zed\ProductStorage\Communication\Plugin\ProductManagement\StorageProductAbstractReadinessProviderPlugin;
use Spryker\Zed\ProductStorage\Dependency\Facade\ProductStorageToStoreFacadeInterface;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group ProductStorage
 * @group Communication
 * @group Plugin
 * @group StorageProductAbstractReadinessProviderPluginTest
 * Add your own group annotations below this line
 */
class StorageProductAbstractReadinessProviderPluginTest extends Unit
{
    /**
     * @return void
     */
    public function testProvideFormatsStoresAndLocales(): void
    {
        // Arrange
        $idProductAbstract = 123;
        $plugin = $this->createPluginWithMocks(
            $this->createProductStorageClientMockForLocales($idProductAbstract, [
                'DE' => ['de_DE'],
                'US' => [],
            ]),
            $this->createStoreFacadeMockWithLocales([
                'DE' => ['de_DE', 'en_US'],
                'US' => ['en_US'],
            ]),
        );

        $requestTransfer = (new ProductAbstractReadinessRequestTransfer())
            ->setProductAbstract((new ProductAbstractTransfer())->setIdProductAbstract($idProductAbstract));

        // Act
        $result = $plugin->provide($requestTransfer, new ArrayObject());

        // Assert
        $this->assertCount(1, $result);
        $productReadiness = $result[0];
        $this->assertSame('In Storage for store/locale', $productReadiness->getTitle());
        $this->assertSame('DE: de_DE | US: -', $productReadiness->getValues()[0]);
    }

    /**
     * @return void
     */
    public function testProvideReturnsNoWhenNoStoresProvided(): void
    {
        // Arrange
        $idProductAbstract = 456;
        $plugin = $this->createPluginWithMocks(
            $this->createProductStorageClientMockForLocales($idProductAbstract, []),
            $this->createEmptyStoreFacadeMock(),
        );

        $requestTransfer = (new ProductAbstractReadinessRequestTransfer())
            ->setProductAbstract((new ProductAbstractTransfer())->setIdProductAbstract($idProductAbstract));

        // Act
        $result = $plugin->provide($requestTransfer, new ArrayObject());

        // Assert
        $this->assertSame('-', $result[0]->getValues()[0]);
    }

    /**
     * @param \Spryker\Client\ProductStorage\ProductStorageClientInterface $clientMock
     * @param \Spryker\Zed\ProductStorage\Dependency\Facade\ProductStorageToStoreFacadeInterface $storeFacadeMock
     *
     * @return \Spryker\Zed\ProductStorage\Communication\Plugin\ProductManagement\StorageProductAbstractReadinessProviderPlugin
     */
    protected function createPluginWithMocks(
        ProductStorageClientInterface $clientMock,
        ProductStorageToStoreFacadeInterface $storeFacadeMock
    ): StorageProductAbstractReadinessProviderPlugin {
        $provider = new StorageProductAbstractReadinessProvider($clientMock, $storeFacadeMock);

        $factoryMock = $this->getMockBuilder(ProductStorageBusinessFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['createStorageProductAbstractReadinessProvider'])
            ->getMock();

        $factoryMock->method('createStorageProductAbstractReadinessProvider')->willReturn($provider);

        $plugin = new StorageProductAbstractReadinessProviderPlugin();
        $plugin->setBusinessFactory($factoryMock);

        return $plugin;
    }

    /**
     * @param int $idProductAbstract
     * @param array<string, array<string>> $storeToLocalesWithData
     *
     * @return \Spryker\Client\ProductStorage\ProductStorageClientInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createProductStorageClientMockForLocales(
        int $idProductAbstract,
        array $storeToLocalesWithData
    ): ProductStorageClientInterface {
        $clientMock = $this->getMockBuilder(ProductStorageClientInterface::class)
            ->getMock();

        $clientMock->method('getBulkProductAbstractStorageDataByProductAbstractIdsForLocaleNameAndStore')
            ->willReturnCallback(function (array $ids, string $locale, string $store) use ($idProductAbstract, $storeToLocalesWithData) {
                if ($ids !== [$idProductAbstract]) {
                    return [];
                }
                $localesWithData = $storeToLocalesWithData[$store] ?? [];

                return in_array($locale, $localesWithData, true) ? ['data'] : [];
            });

        return $clientMock;
    }

    /**
     * @param array<string, array<string>> $storeToAvailableLocales
     *
     * @return \Spryker\Zed\ProductStorage\Dependency\Facade\ProductStorageToStoreFacadeInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createStoreFacadeMockWithLocales(array $storeToAvailableLocales): ProductStorageToStoreFacadeInterface
    {
        $storeFacadeMock = $this->getMockBuilder(ProductStorageToStoreFacadeInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAllStores', 'getStoreByName'])
            ->getMock();

        $stores = [];
        foreach ($storeToAvailableLocales as $storeName => $locales) {
            $store = (new StoreTransfer())
                ->setName($storeName)
                ->setAvailableLocaleIsoCodes($locales);
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

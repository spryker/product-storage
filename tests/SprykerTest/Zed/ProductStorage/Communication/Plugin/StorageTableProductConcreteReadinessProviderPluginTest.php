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
use Spryker\Zed\ProductStorage\Business\ProductStorageBusinessFactory;
use Spryker\Zed\ProductStorage\Business\Provider\StorageTableProductConcreteReadinessProvider;
use Spryker\Zed\ProductStorage\Communication\Plugin\ProductManagement\StorageTableProductConcreteReadinessProviderPlugin;
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
 * @group StorageTableProductConcreteReadinessProviderPluginTest
 * Add your own group annotations below this line
 */
class StorageTableProductConcreteReadinessProviderPluginTest extends Unit
{
    /**
     * @return void
     */
    public function testProvideFormatsLocales(): void
    {
        // Arrange
        $idProductConcrete = 123;
        $plugin = $this->createPluginWithMocks(
            $this->createProductStorageRepositoryMockWithLocales(['de_DE', 'en_US', 'fr_FR']),
        );

        $requestTransfer = (new ProductConcreteReadinessRequestTransfer())
            ->setProductConcrete((new ProductConcreteTransfer())->setIdProductConcrete($idProductConcrete));

        // Act
        $result = $plugin->provide($requestTransfer, new ArrayObject());

        // Assert
        $this->assertCount(1, $result);
        $productReadiness = $result[0];
        $this->assertSame('In Storage table for locale', $productReadiness->getTitle());
        $this->assertSame('de_DE, en_US, fr_FR', $productReadiness->getValues()[0]);
    }

    /**
     * @return void
     */
    public function testProvideReturnsDashWhenNoLocalesProvided(): void
    {
        // Arrange
        $plugin = $this->createPluginWithMocks(
            $this->createProductStorageRepositoryMockWithNoData(),
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
    public function testProvideRemovesDuplicateLocales(): void
    {
        // Arrange
        $idProductConcrete = 789;
        $plugin = $this->createPluginWithMocks(
            $this->createProductStorageRepositoryMockWithLocales(['de_DE', 'en_US', 'de_DE']),
        );

        $requestTransfer = (new ProductConcreteReadinessRequestTransfer())
            ->setProductConcrete((new ProductConcreteTransfer())->setIdProductConcrete($idProductConcrete));

        // Act
        $result = $plugin->provide($requestTransfer, new ArrayObject());

        // Assert
        $this->assertSame('de_DE, en_US', $result[0]->getValues()[0]);
    }

    /**
     * @param \Spryker\Zed\ProductStorage\Persistence\ProductStorageRepositoryInterface $repositoryMock
     *
     * @return \Spryker\Zed\ProductStorage\Communication\Plugin\ProductManagement\StorageTableProductConcreteReadinessProviderPlugin
     */
    protected function createPluginWithMocks(
        ProductStorageRepositoryInterface $repositoryMock
    ): StorageTableProductConcreteReadinessProviderPlugin {
        $storeFacadeMock = $this->createEmptyStoreFacadeMock();

        $provider = new StorageTableProductConcreteReadinessProvider(
            $repositoryMock,
            $storeFacadeMock,
        );

        $factoryMock = $this->getMockBuilder(ProductStorageBusinessFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['createStorageTableProductConcreteReadinessProvider'])
            ->getMock();

        $factoryMock->method('createStorageTableProductConcreteReadinessProvider')->willReturn($provider);

        $plugin = new StorageTableProductConcreteReadinessProviderPlugin();
        $plugin->setBusinessFactory($factoryMock);

        return $plugin;
    }

    /**
     * @param array<string> $localeNames
     *
     * @return \Spryker\Zed\ProductStorage\Persistence\ProductStorageRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createProductStorageRepositoryMockWithLocales(array $localeNames): ProductStorageRepositoryInterface
    {
        $repositoryMock = $this->getMockBuilder(ProductStorageRepositoryInterface::class)
            ->getMock();

        $productStorageData = [];
        foreach ($localeNames as $localeName) {
            $productStorageData[] = [
                'fk_product' => 123,
                'locale' => $localeName,
                'data' => '{}',
            ];
        }

        $repositoryMock->method('getProductConcretesByIds')->willReturn($productStorageData);

        return $repositoryMock;
    }

    /**
     * @return \Spryker\Zed\ProductStorage\Persistence\ProductStorageRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createProductStorageRepositoryMockWithNoData(): ProductStorageRepositoryInterface
    {
        $repositoryMock = $this->getMockBuilder(ProductStorageRepositoryInterface::class)
            ->getMock();

        $repositoryMock->method('getProductConcretesByIds')->willReturn([]);

        return $repositoryMock;
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

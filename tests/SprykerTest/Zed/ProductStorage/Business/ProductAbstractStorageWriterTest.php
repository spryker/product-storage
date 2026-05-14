<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\ProductStorage\Business;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\StoreTransfer;
use Orm\Zed\ProductStorage\Persistence\SpyProductAbstractStorage;
use ReflectionClass;
use Spryker\Shared\ProductStorage\ProductStorageConfig as SharedProductStorageConfig;
use Spryker\Zed\ProductStorage\Business\Attribute\AttributeMapInterface;
use Spryker\Zed\ProductStorage\Business\Storage\ProductAbstractStorageWriter;
use Spryker\Zed\ProductStorage\Dependency\Facade\ProductStorageToProductInterface;
use Spryker\Zed\ProductStorage\Dependency\Facade\ProductStorageToStoreFacadeInterface;
use Spryker\Zed\ProductStorage\Persistence\ProductStorageQueryContainerInterface;
use Spryker\Zed\ProductStorage\Persistence\ProductStorageRepositoryInterface;
use Spryker\Zed\ProductStorage\ProductStorageConfig;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group ProductStorage
 * @group Business
 * @group ProductAbstractStorageWriterTest
 * Add your own group annotations below this line
 */
class ProductAbstractStorageWriterTest extends Unit
{
    protected const int PRODUCT_ABSTRACT_ID = 42;

    protected const string LOCALE_NAME_DE = 'de_DE';

    protected const string LOCALE_NAME_EN = 'en_US';

    protected const string STORE_NAME_DE = 'DE';

    protected const string STORE_NAME_AT = 'AT';

    /**
     * @var \SprykerTest\Zed\ProductStorage\ProductStorageBusinessTester
     */
    protected $tester;

    public function testGivenUnifiedDisabledWhenPairingThenCreatesOnePairPerStore(): void
    {
        // Arrange
        $writer = $this->buildWriter(isUnifiedEnabled: false, validLocalesPerStore: [
            static::STORE_NAME_DE => [static::LOCALE_NAME_DE],
            static::STORE_NAME_AT => [static::LOCALE_NAME_DE],
        ]);
        $localizedEntities = [$this->buildLocalizedEntity(static::LOCALE_NAME_DE, [static::STORE_NAME_DE, static::STORE_NAME_AT])];

        // Act
        $pairs = $this->callPairing($writer, $localizedEntities, []);

        // Assert
        $storeNames = array_column($pairs, ProductAbstractStorageWriter::STORE_NAME);
        $this->assertContains(static::STORE_NAME_DE, $storeNames);
        $this->assertContains(static::STORE_NAME_AT, $storeNames);
        $this->assertCount(2, $pairs);
    }

    public function testGivenUnifiedEnabledWhenPairingThenCreatesOnePairPerLocale(): void
    {
        // Arrange
        $writer = $this->buildWriter(isUnifiedEnabled: true, validLocalesPerStore: [
            static::STORE_NAME_DE => [static::LOCALE_NAME_DE],
            static::STORE_NAME_AT => [static::LOCALE_NAME_DE],
        ]);
        $localizedEntities = [$this->buildLocalizedEntity(static::LOCALE_NAME_DE, [static::STORE_NAME_DE, static::STORE_NAME_AT])];

        // Act
        $pairs = $this->callPairing($writer, $localizedEntities, []);

        // Assert
        $this->assertCount(1, $pairs);
        $this->assertSame(SharedProductStorageConfig::PRODUCT_ABSTRACT_STORAGE_UNIFIED_STORE_KEY, $pairs[0][ProductAbstractStorageWriter::STORE_NAME]);
    }

    public function testGivenUnifiedEnabledWhenPairingThenStoresMapContainsAllValidStores(): void
    {
        // Arrange
        $writer = $this->buildWriter(isUnifiedEnabled: true, validLocalesPerStore: [
            static::STORE_NAME_DE => [static::LOCALE_NAME_DE],
            static::STORE_NAME_AT => [static::LOCALE_NAME_DE],
        ]);
        $localizedEntities = [$this->buildLocalizedEntity(static::LOCALE_NAME_DE, [static::STORE_NAME_DE, static::STORE_NAME_AT])];

        // Act
        $pairs = $this->callPairing($writer, $localizedEntities, []);

        // Assert
        $storesMap = $pairs[0]['STORES_MAP'];
        $this->assertContains(static::STORE_NAME_DE, $storesMap);
        $this->assertContains(static::STORE_NAME_AT, $storesMap);
    }

    public function testGivenUnifiedEnabledWhenLocaleInvalidForOneStoreThenOnlyValidStoresInMap(): void
    {
        // Arrange — DE supports de_DE but AT does not
        $writer = $this->buildWriter(isUnifiedEnabled: true, validLocalesPerStore: [
            static::STORE_NAME_DE => [static::LOCALE_NAME_DE],
            static::STORE_NAME_AT => [],
        ]);
        $localizedEntities = [$this->buildLocalizedEntity(static::LOCALE_NAME_DE, [static::STORE_NAME_DE, static::STORE_NAME_AT])];

        // Act
        $pairs = $this->callPairing($writer, $localizedEntities, []);

        // Assert
        $this->assertCount(1, $pairs);
        $this->assertSame([static::STORE_NAME_DE], $pairs[0]['STORES_MAP']);
    }

    public function testGivenUnifiedEnabledWhenLocaleInvalidForAllStoresThenNoPairCreated(): void
    {
        // Arrange — no store supports the locale
        $writer = $this->buildWriter(isUnifiedEnabled: true, validLocalesPerStore: [
            static::STORE_NAME_DE => [],
            static::STORE_NAME_AT => [],
        ]);
        $localizedEntities = [$this->buildLocalizedEntity(static::LOCALE_NAME_DE, [static::STORE_NAME_DE, static::STORE_NAME_AT])];

        // Act
        $pairs = $this->callPairing($writer, $localizedEntities, []);

        // Assert
        $this->assertCount(0, $pairs);
    }

    public function testGivenUnifiedEnabledWhenExistingUnifiedEntityFoundThenItIsReused(): void
    {
        // Arrange
        $writer = $this->buildWriter(isUnifiedEnabled: true, validLocalesPerStore: [
            static::STORE_NAME_DE => [static::LOCALE_NAME_DE],
        ]);
        $localizedEntities = [$this->buildLocalizedEntity(static::LOCALE_NAME_DE, [static::STORE_NAME_DE])];

        $existingEntity = new SpyProductAbstractStorage();
        $existingEntity->setFkProductAbstract(static::PRODUCT_ABSTRACT_ID);
        $existingEntity->setStore(SharedProductStorageConfig::PRODUCT_ABSTRACT_STORAGE_UNIFIED_STORE_KEY);
        $existingEntity->setLocale(static::LOCALE_NAME_DE);

        $storageEntities = [
            static::PRODUCT_ABSTRACT_ID => [
                SharedProductStorageConfig::PRODUCT_ABSTRACT_STORAGE_UNIFIED_STORE_KEY => [
                    static::LOCALE_NAME_DE => $existingEntity,
                ],
            ],
        ];

        // Act
        $pairs = $this->callPairing($writer, $localizedEntities, $storageEntities);

        // Assert
        $this->assertCount(1, $pairs);
        $this->assertSame($existingEntity, $pairs[0][ProductAbstractStorageWriter::PRODUCT_ABSTRACT_STORAGE_ENTITY]);
    }

    /**
     * @param array<string, array<string>> $validLocalesPerStore
     */
    protected function buildWriter(bool $isUnifiedEnabled, array $validLocalesPerStore): ProductAbstractStorageWriter
    {
        $storeFacadeMock = $this->createMock(ProductStorageToStoreFacadeInterface::class);
        $storeFacadeMock->method('getStoreByName')->willReturnCallback(
            function (string $storeName) use ($validLocalesPerStore): StoreTransfer {
                return (new StoreTransfer())->setAvailableLocaleIsoCodes($validLocalesPerStore[$storeName] ?? []);
            },
        );

        $configMock = $this->createMock(ProductStorageConfig::class);
        $configMock->method('isSendingToQueue')->willReturn(false);
        $configMock->method('isProductAbstractStorageUnifiedEnabled')->willReturn($isUnifiedEnabled);

        return new ProductAbstractStorageWriter(
            $this->createMock(ProductStorageToProductInterface::class),
            $this->createMock(AttributeMapInterface::class),
            $this->createMock(ProductStorageQueryContainerInterface::class),
            $storeFacadeMock,
            $this->createMock(ProductStorageRepositoryInterface::class),
            $configMock,
            [],
            [],
        );
    }

    /**
     * @param array<string> $storeNames
     *
     * @return array<string, mixed>
     */
    protected function buildLocalizedEntity(string $localeName, array $storeNames): array
    {
        $stores = array_map(
            static fn (string $name): array => ['SpyStore' => ['name' => $name]],
            $storeNames,
        );

        return [
            'SpyProductAbstract' => [
                'id_product_abstract' => static::PRODUCT_ABSTRACT_ID,
                'SpyProductAbstractStores' => $stores,
            ],
            'Locale' => [
                'locale_name' => $localeName,
                'id_locale' => 1,
            ],
        ];
    }

    /**
     * @param array<\Orm\Zed\ProductStorage\Persistence\SpyProductAbstractStorage> $existingStorageEntities
     *
     * @return array<array<string, mixed>>
     */
    protected function callPairing(
        ProductAbstractStorageWriter $writer,
        array $localizedEntities,
        array $existingStorageEntities
    ): array {
        $flatEntities = [];
        foreach ($existingStorageEntities as $byProductId) {
            foreach ($byProductId as $byStore) {
                foreach ($byStore as $entity) {
                    $flatEntities[] = $entity;
                }
            }
        }

        $pairMethod = (new ReflectionClass($writer))
            ->getMethod('pairProductAbstractLocalizedEntitiesWithProductAbstractStorageEntities');

        return $pairMethod->invoke($writer, $localizedEntities, $flatEntities);
    }
}

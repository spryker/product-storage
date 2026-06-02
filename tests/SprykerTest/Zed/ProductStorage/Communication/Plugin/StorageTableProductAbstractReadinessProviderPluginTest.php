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
use Spryker\Client\ProductStorage\ProductStorageClientInterface;
use Spryker\Zed\ProductStorage\Business\ProductStorageBusinessFactory;
use Spryker\Zed\ProductStorage\Business\Provider\StorageTableProductAbstractReadinessProvider;
use Spryker\Zed\ProductStorage\Communication\Plugin\ProductManagement\StorageTableProductAbstractReadinessProviderPlugin;
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
    protected const string STORAGE_KEY = 'product_abstract:de:de_de:123';

    protected const string STORAGE_KEY_URL_PART = '/storage-gui/maintenance/key?key=';

    public function testProvideReturnsFallbackWhenNoDataExists(): void
    {
        // Arrange
        $plugin = $this->createPlugin(
            $this->createRepositoryMockReturning([], []),
            $this->createStorageClientMockReturning([]),
        );

        // Act
        $result = $plugin->provide($this->createRequest(456), new ArrayObject());

        // Assert
        $this->assertSame('-', $result[0]->getValues()[0]);
    }

    public function testProvideReturnsStorageKeyLinkWhenStorageKeyExists(): void
    {
        // Arrange
        $plugin = $this->createPlugin(
            $this->createRepositoryMockReturning(
                [['Locale' => ['locale_name' => 'de_DE'], 'updated_at' => null]],
                [$this->buildStorageEntry('de_DE', 'DE', static::STORAGE_KEY, null, null)],
            ),
            $this->createStorageClientMockReturning([]),
        );

        // Act
        $result = $plugin->provide($this->createRequest(123), new ArrayObject());

        // Assert
        $this->assertStringContainsString(static::STORAGE_KEY_URL_PART . static::STORAGE_KEY, $result[0]->getValues()[0]);
    }

    public function testProvideReturnsSyncedStatusWhenStorageMatchesDatabase(): void
    {
        // Arrange
        $dbData = ['id_product_abstract' => 123, 'name' => 'Test'];
        $plugin = $this->createPlugin(
            $this->createRepositoryMockReturning(
                [['Locale' => ['locale_name' => 'de_DE'], 'updated_at' => '2024-01-15 10:30:00']],
                [$this->buildStorageEntry('de_DE', 'DE', static::STORAGE_KEY, $dbData, '2024-01-15 10:30:00')],
            ),
            $this->createStorageClientMockReturning([
                'kv:' . static::STORAGE_KEY => json_encode(array_merge($dbData, ['_timestamp' => 1705315800])),
            ]),
        );

        // Act
        $result = $plugin->provide($this->createRequest(123), new ArrayObject());

        // Assert
        $row = $result[0]->getValues()[0];
        $this->assertStringContainsString('Synced', $row);
        $this->assertStringContainsString('de_DE', $row);
        $this->assertStringContainsString('DE', $row);
        $this->assertStringContainsString('2024-01-15 10:30:00 UTC', $row);
    }

    public function testProvideReturnsUnsyncedStatusWhenStorageKeyIsMissing(): void
    {
        // Arrange - storage key present in DB row but storage returns nothing for it
        $plugin = $this->createPlugin(
            $this->createRepositoryMockReturning(
                [['Locale' => ['locale_name' => 'de_DE'], 'updated_at' => null]],
                [$this->buildStorageEntry('de_DE', 'DE', static::STORAGE_KEY, ['name' => 'Test'], null)],
            ),
            $this->createStorageClientMockReturning([]),
        );

        // Act
        $result = $plugin->provide($this->createRequest(123), new ArrayObject());

        // Assert
        $row = $result[0]->getValues()[0];
        $this->assertStringContainsString('Unsynced', $row);
        $this->assertStringContainsString(static::STORAGE_KEY_URL_PART . static::STORAGE_KEY, $row);
    }

    public function testProvideReturnsUnsyncedStatusWhenRowHasNoStorageKey(): void
    {
        // Arrange - no key column means the product was never synced to storage
        $plugin = $this->createPlugin(
            $this->createRepositoryMockReturning(
                [['Locale' => ['locale_name' => 'en_US'], 'updated_at' => null]],
                [$this->buildStorageEntry('en_US', 'US', null, null, null)],
            ),
            $this->createStorageClientMockReturning([]),
        );

        // Act
        $result = $plugin->provide($this->createRequest(123), new ArrayObject());

        // Assert
        $row = $result[0]->getValues()[0];
        $this->assertStringContainsString('Unsynced', $row);
        // No clickable link when the storage key is absent
        $this->assertStringNotContainsString(static::STORAGE_KEY_URL_PART, $row);
    }

    public function testProvideReturnsUnsyncedStatusWhenDataDiffersFromStorage(): void
    {
        // Arrange
        $dbData = ['name' => 'Original'];
        $storageData = ['name' => 'Different'];
        $plugin = $this->createPlugin(
            $this->createRepositoryMockReturning(
                [['Locale' => ['locale_name' => 'de_DE'], 'updated_at' => null]],
                [$this->buildStorageEntry('de_DE', 'DE', static::STORAGE_KEY, $dbData, null)],
            ),
            $this->createStorageClientMockReturning([
                'kv:' . static::STORAGE_KEY => json_encode(array_merge($storageData, ['_timestamp' => 1705315800])),
            ]),
        );

        // Act
        $result = $plugin->provide($this->createRequest(123), new ArrayObject());

        // Assert
        $this->assertStringContainsString('Unsynced', $result[0]->getValues()[0]);
    }

    public function testProvideIncludesDbOnlyLocaleWhenNotInStorage(): void
    {
        // Arrange - locale exists in the DB locale table but has no storage entry yet
        $plugin = $this->createPlugin(
            $this->createRepositoryMockReturning(
                [['Locale' => ['locale_name' => 'de_DE'], 'updated_at' => '2024-01-15 10:30:00']],
                [],
            ),
            $this->createStorageClientMockReturning([]),
        );

        // Act
        $result = $plugin->provide($this->createRequest(123), new ArrayObject());

        // Assert - missing storage entry produces an Unsynced row with the DB timestamp
        $row = $result[0]->getValues()[0];
        $this->assertStringContainsString('Unsynced', $row);
        $this->assertStringContainsString('de_DE', $row);
        $this->assertStringContainsString('2024-01-15 10:30:00 UTC', $row);
    }

    public function testProvideReturnsOneValuePerStorageRow(): void
    {
        // Arrange
        $plugin = $this->createPlugin(
            $this->createRepositoryMockReturning(
                [
                    ['Locale' => ['locale_name' => 'de_DE'], 'updated_at' => null],
                    ['Locale' => ['locale_name' => 'en_US'], 'updated_at' => null],
                ],
                [
                    $this->buildStorageEntry('de_DE', 'DE', null, null, null),
                    $this->buildStorageEntry('en_US', 'US', null, null, null),
                ],
            ),
            $this->createStorageClientMockReturning([]),
        );

        // Act
        $result = $plugin->provide($this->createRequest(123), new ArrayObject());

        // Assert
        $this->assertCount(2, $result[0]->getValues());
    }

    protected function createPlugin(
        ProductStorageRepositoryInterface $repositoryMock,
        ProductStorageClientInterface $storageClientMock,
    ): StorageTableProductAbstractReadinessProviderPlugin {
        $provider = new StorageTableProductAbstractReadinessProvider(
            $repositoryMock,
            $storageClientMock,
        );

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
     * @param array<array<string, mixed>> $localeData
     * @param array<array<string, mixed>> $storageEntries
     *
     * @return \Spryker\Zed\ProductStorage\Persistence\ProductStorageRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createRepositoryMockReturning(array $localeData, array $storageEntries): ProductStorageRepositoryInterface
    {
        $mock = $this->getMockBuilder(ProductStorageRepositoryInterface::class)->getMock();
        $mock->method('getProductAbstractsByIds')->willReturn($localeData);
        $mock->method('getProductAbstractStorageEntriesByIdProductAbstract')->willReturn($storageEntries);

        return $mock;
    }

    /**
     * @return \Spryker\Client\ProductStorage\ProductStorageClientInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createStorageClientMockReturning(array $data): ProductStorageClientInterface
    {
        $mock = $this->getMockBuilder(ProductStorageClientInterface::class)->getMock();
        $mock->method('getRawProductCollection')->willReturn($data);

        return $mock;
    }

    protected function createRequest(int $idProductAbstract): ProductAbstractReadinessRequestTransfer
    {
        return (new ProductAbstractReadinessRequestTransfer())
            ->setProductAbstract((new ProductAbstractTransfer())->setIdProductAbstract($idProductAbstract));
    }

    /**
     * @param array<string, mixed>|null $data
     */
    protected function buildStorageEntry(string $locale, string $store, ?string $storageKey, ?array $data, ?string $updatedAt): array
    {
        return [
            'locale' => $locale,
            'store' => $store,
            'key' => $storageKey,
            'data' => $data,
            'updated_at' => $updatedAt,
        ];
    }
}

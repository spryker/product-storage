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
use Spryker\Client\ProductStorage\ProductStorageClientInterface;
use Spryker\Zed\ProductStorage\Business\ProductStorageBusinessFactory;
use Spryker\Zed\ProductStorage\Business\Provider\StorageTableProductConcreteReadinessProvider;
use Spryker\Zed\ProductStorage\Communication\Plugin\ProductManagement\StorageTableProductConcreteReadinessProviderPlugin;
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
    protected const string STORAGE_KEY = 'product_concrete:de_de:123';

    protected const string STORAGE_KEY_URL_PART = '/storage-gui/maintenance/key?key=';

    public function testProvideReturnsFallbackWhenNoDataExists(): void
    {
        // Arrange
        $plugin = $this->createPlugin(
            $this->createRepositoryMockReturning([]),
            $this->createStorageClientMockReturning([]),
        );

        // Act
        $result = $plugin->provide($this->createRequest(456), new ArrayObject());

        // Assert
        $this->assertCount(1, $result->getArrayCopy());
        $this->assertSame('-', $result->getArrayCopy()[0]->getValues()[0]);
    }

    public function testProvideReturnsStorageKeyLinkWhenStorageKeyExists(): void
    {
        // Arrange
        $plugin = $this->createPlugin(
            $this->createRepositoryMockReturning([
                $this->buildStorageRow('de_DE', static::STORAGE_KEY, null, null),
            ]),
            $this->createStorageClientMockReturning([]),
        );

        // Act
        $result = $plugin->provide($this->createRequest(123), new ArrayObject());

        // Assert
        $row = $result->getArrayCopy()[0]->getValues()[0];
        $this->assertStringContainsString(static::STORAGE_KEY_URL_PART . static::STORAGE_KEY, $row);
    }

    public function testProvideReturnsSyncedStatusWhenStorageMatchesDatabase(): void
    {
        // Arrange
        $dbData = ['id_product' => 123, 'sku' => 'SKU-123', 'name' => 'Test'];
        $plugin = $this->createPlugin(
            $this->createRepositoryMockReturning([
                $this->buildStorageRow('de_DE', static::STORAGE_KEY, $dbData, '2024-01-15 10:30:00'),
            ]),
            $this->createStorageClientMockReturning([
                'kv:' . static::STORAGE_KEY => json_encode(array_merge($dbData, ['_timestamp' => 1705315800])),
            ]),
        );

        // Act
        $result = $plugin->provide($this->createRequest(123), new ArrayObject());

        // Assert
        $row = $result->getArrayCopy()[0]->getValues()[0];
        $this->assertStringContainsString('Synced', $row);
        $this->assertStringContainsString('de_DE', $row);
        $this->assertStringContainsString('2024-01-15 10:30:00 UTC', $row);
    }

    public function testProvideReturnsUnsyncedStatusWhenStorageKeyIsMissing(): void
    {
        // Arrange - storage key present in DB row but storage returns nothing for it
        $plugin = $this->createPlugin(
            $this->createRepositoryMockReturning([
                $this->buildStorageRow('de_DE', static::STORAGE_KEY, ['sku' => 'SKU-123'], null),
            ]),
            $this->createStorageClientMockReturning([]),
        );

        // Act
        $result = $plugin->provide($this->createRequest(123), new ArrayObject());

        // Assert
        $row = $result->getArrayCopy()[0]->getValues()[0];
        $this->assertStringContainsString('Unsynced', $row);
        $this->assertStringContainsString(static::STORAGE_KEY_URL_PART . static::STORAGE_KEY, $row);
    }

    public function testProvideReturnsUnsyncedStatusWhenRowHasNoStorageKey(): void
    {
        // Arrange - no key column means product was never synced to storage
        $plugin = $this->createPlugin(
            $this->createRepositoryMockReturning([
                $this->buildStorageRow('en_US', null, null, null),
            ]),
            $this->createStorageClientMockReturning([]),
        );

        // Act
        $result = $plugin->provide($this->createRequest(123), new ArrayObject());

        // Assert
        $row = $result->getArrayCopy()[0]->getValues()[0];
        $this->assertStringContainsString('Unsynced', $row);
        // No link when key is absent
        $this->assertStringNotContainsString(static::STORAGE_KEY_URL_PART, $row);
    }

    public function testProvideReturnsUnsyncedStatusWhenDataDiffersFromStorage(): void
    {
        // Arrange
        $dbData = ['sku' => 'SKU-123'];
        $storageData = ['sku' => 'SKU-DIFFERENT'];
        $plugin = $this->createPlugin(
            $this->createRepositoryMockReturning([
                $this->buildStorageRow('de_DE', static::STORAGE_KEY, $dbData, null),
            ]),
            $this->createStorageClientMockReturning([
                'kv:' . static::STORAGE_KEY => json_encode(array_merge($storageData, ['_timestamp' => 1705315800])),
            ]),
        );

        // Act
        $result = $plugin->provide($this->createRequest(123), new ArrayObject());

        // Assert
        $this->assertStringContainsString('Unsynced', $result->getArrayCopy()[0]->getValues()[0]);
    }

    public function testProvideReturnsOneValuePerStorageRow(): void
    {
        // Arrange
        $plugin = $this->createPlugin(
            $this->createRepositoryMockReturning([
                $this->buildStorageRow('de_DE', null, null, null),
                $this->buildStorageRow('en_US', null, null, null),
            ]),
            $this->createStorageClientMockReturning([]),
        );

        // Act
        $result = $plugin->provide($this->createRequest(123), new ArrayObject());

        // Assert
        $this->assertCount(2, $result->getArrayCopy()[0]->getValues());
    }

    protected function createPlugin(
        ProductStorageRepositoryInterface $repositoryMock,
        ProductStorageClientInterface $storageClientMock,
    ): StorageTableProductConcreteReadinessProviderPlugin {
        $provider = new StorageTableProductConcreteReadinessProvider(
            $repositoryMock,
            $storageClientMock,
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
     * @param array<array<string, mixed>> $data
     *
     * @return \Spryker\Zed\ProductStorage\Persistence\ProductStorageRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createRepositoryMockReturning(array $data): ProductStorageRepositoryInterface
    {
        $mock = $this->getMockBuilder(ProductStorageRepositoryInterface::class)->getMock();
        $mock->method('getProductConcretesByIds')->willReturn($data);

        return $mock;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return \Spryker\Client\ProductStorage\ProductStorageClientInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createStorageClientMockReturning(array $data): ProductStorageClientInterface
    {
        $mock = $this->getMockBuilder(ProductStorageClientInterface::class)->getMock();
        $mock->method('getRawProductCollection')->willReturn($data);

        return $mock;
    }

    protected function createRequest(int $idProductConcrete): ProductConcreteReadinessRequestTransfer
    {
        return (new ProductConcreteReadinessRequestTransfer())
            ->setProductConcrete((new ProductConcreteTransfer())->setIdProductConcrete($idProductConcrete));
    }

    /**
     * @param array<string, mixed>|null $data
     */
    protected function buildStorageRow(string $locale, ?string $storageKey, ?array $data, ?string $updatedAt): array
    {
        return [
            'locale' => $locale,
            'key' => $storageKey,
            'data' => $data,
            'updated_at' => $updatedAt,
        ];
    }
}

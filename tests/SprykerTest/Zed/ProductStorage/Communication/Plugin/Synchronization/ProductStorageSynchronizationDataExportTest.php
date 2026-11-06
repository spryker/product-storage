<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\ProductStorage\Communication\Plugin\Synchronization;

use Codeception\Test\Unit;
use Spryker\Zed\ProductStorage\Communication\Plugin\Synchronization\ProductAbstractSynchronizationDataPlugin;
use Spryker\Zed\ProductStorage\Communication\Plugin\Synchronization\ProductConcreteSynchronizationDataPlugin;
use Spryker\Zed\Synchronization\Communication\Plugin\Synchronization\SynchronizationDataQueryExpanderWhereBetweenStrategyPlugin;
use Spryker\Zed\SynchronizationExtension\Dependency\Plugin\SynchronizationDataQueryContainerPluginInterface;
use SprykerTest\Zed\ProductStorage\ProductStorageCommunicationTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group ProductStorage
 * @group Communication
 * @group Plugin
 * @group Synchronization
 * @group ProductStorageSynchronizationDataExportTest
 * Add your own group annotations below this line
 */
class ProductStorageSynchronizationDataExportTest extends Unit
{
    protected ProductStorageCommunicationTester $tester;

    /**
     * @dataProvider synchronizationDataPluginDataProvider
     */
    public function testExportsAllRowsWhenLowestPrimaryKeyIsAboveChunkSize(SynchronizationDataQueryContainerPluginInterface $synchronizationDataPlugin): void
    {
        // Arrange
        $expectedKeys = $this->tester->haveOnlySynchronizationEntitiesWithPrimaryKeys($synchronizationDataPlugin, [1001, 1002, 1003]);

        // Act
        $sentMessageKeys = $this->tester->exportSynchronizationData($synchronizationDataPlugin, new SynchronizationDataQueryExpanderWhereBetweenStrategyPlugin());

        // Assert
        $this->tester->assertSynchronizationKeysExported($expectedKeys, $sentMessageKeys);
    }

    /**
     * @dataProvider synchronizationDataPluginDataProvider
     */
    public function testExportsRowsLocatedAfterPrimaryKeyGapLargerThanChunkSize(
        SynchronizationDataQueryContainerPluginInterface $synchronizationDataPlugin
    ): void {
        // Arrange
        $expectedKeys = $this->tester->haveOnlySynchronizationEntitiesWithPrimaryKeys($synchronizationDataPlugin, [1, 2, 500, 501]);

        // Act
        $sentMessageKeys = $this->tester->exportSynchronizationData($synchronizationDataPlugin, new SynchronizationDataQueryExpanderWhereBetweenStrategyPlugin());

        // Assert
        $this->tester->assertSynchronizationKeysExported($expectedKeys, $sentMessageKeys);
    }

    /**
     * @dataProvider synchronizationDataPluginDataProvider
     */
    public function testExportsAllRowsWhenLowestPrimaryKeyIsAboveChunkSizeWithKeysetPagination(
        SynchronizationDataQueryContainerPluginInterface $synchronizationDataPlugin
    ): void {
        // Arrange
        $expectedKeys = $this->tester->haveOnlySynchronizationEntitiesWithPrimaryKeys($synchronizationDataPlugin, [1001, 1002, 1003]);

        // Act
        $sentMessageKeys = $this->tester->exportSynchronizationDataWithKeysetPagination($synchronizationDataPlugin);

        // Assert
        $this->tester->assertSynchronizationKeysExported($expectedKeys, $sentMessageKeys);
    }

    /**
     * @dataProvider synchronizationDataPluginDataProvider
     */
    public function testExportsRowsLocatedAfterPrimaryKeyGapLargerThanChunkSizeWithKeysetPagination(
        SynchronizationDataQueryContainerPluginInterface $synchronizationDataPlugin
    ): void {
        // Arrange
        $expectedKeys = $this->tester->haveOnlySynchronizationEntitiesWithPrimaryKeys($synchronizationDataPlugin, [1, 2, 500, 501]);

        // Act
        $sentMessageKeys = $this->tester->exportSynchronizationDataWithKeysetPagination($synchronizationDataPlugin);

        // Assert
        $this->tester->assertSynchronizationKeysExported($expectedKeys, $sentMessageKeys);
    }

    /**
     * @return array<string, array<\Spryker\Zed\SynchronizationExtension\Dependency\Plugin\SynchronizationDataQueryContainerPluginInterface>>
     */
    public static function synchronizationDataPluginDataProvider(): array
    {
        return [
            'product abstract' => [new ProductAbstractSynchronizationDataPlugin()],
            'product concrete' => [new ProductConcreteSynchronizationDataPlugin()],
        ];
    }
}

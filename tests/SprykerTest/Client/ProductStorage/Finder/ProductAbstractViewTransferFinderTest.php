<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Client\ProductStorage\Finder;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\ProductViewTransfer;
use Generated\Shared\Transfer\StoreTransfer;
use ReflectionProperty;
use Spryker\Client\ProductStorage\Dependency\Client\ProductStorageToStoreClientInterface;
use Spryker\Client\ProductStorage\Finder\ProductAbstractViewTransferFinder;
use Spryker\Client\ProductStorage\Mapper\ProductStorageDataMapperInterface;
use Spryker\Client\ProductStorage\Storage\ProductAbstractStorageReaderInterface;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Client
 * @group ProductStorage
 * @group Finder
 * @group ProductAbstractViewTransferFinderTest
 * Add your own group annotations below this line
 */
class ProductAbstractViewTransferFinderTest extends Unit
{
    protected const string KEY_ID_PRODUCT_ABSTRACT = 'id_product_abstract';

    protected const string LOCALE_NAME = 'de_DE';

    protected const string STORE_NAME_DE = 'DE';

    protected const int ID_PRODUCT_ABSTRACT_1 = 1;

    protected const int ID_PRODUCT_ABSTRACT_2 = 2;

    protected const string SUPER_ATTRIBUTE_NAME = 'color';

    protected const string SUPER_ATTRIBUTE_VALUE_1 = 'red';

    protected const string SUPER_ATTRIBUTE_VALUE_2 = 'blue';

    protected function setUp(): void
    {
        parent::setUp();

        (new ReflectionProperty(ProductAbstractViewTransferFinder::class, 'productViewTransfersCache'))
            ->setValue(null, []);
    }

    public function testGetProductViewTransfersReadsStorageOnceWhenSelectedAttributesAreProvidedPerProduct(): void
    {
        // Arrange
        $productAbstractIds = [static::ID_PRODUCT_ABSTRACT_1, static::ID_PRODUCT_ABSTRACT_2];
        $selectedAttributes = [
            static::ID_PRODUCT_ABSTRACT_1 => [static::SUPER_ATTRIBUTE_NAME => static::SUPER_ATTRIBUTE_VALUE_1],
            static::ID_PRODUCT_ABSTRACT_2 => [static::SUPER_ATTRIBUTE_NAME => static::SUPER_ATTRIBUTE_VALUE_2],
        ];
        $productAbstractViewTransferFinder = $this->createProductAbstractViewTransferFinder($productAbstractIds);

        // Act
        $productViewTransfers = $productAbstractViewTransferFinder
            ->getProductViewTransfers($productAbstractIds, static::LOCALE_NAME, $selectedAttributes);
        $cachedProductViewTransfers = $productAbstractViewTransferFinder
            ->getProductViewTransfers($productAbstractIds, static::LOCALE_NAME, $selectedAttributes);

        // Assert
        $this->assertCount(2, $productViewTransfers);
        $this->assertSame($productViewTransfers, $cachedProductViewTransfers);
    }

    public function testGetProductViewTransfersReadsStorageAgainWhenSelectedAttributesChange(): void
    {
        // Arrange
        $productAbstractIds = [static::ID_PRODUCT_ABSTRACT_1];
        $productAbstractViewTransferFinder = $this->createProductAbstractViewTransferFinder($productAbstractIds, 2);

        // Act
        $productViewTransfers = $productAbstractViewTransferFinder->getProductViewTransfers(
            $productAbstractIds,
            static::LOCALE_NAME,
            [static::ID_PRODUCT_ABSTRACT_1 => [static::SUPER_ATTRIBUTE_NAME => static::SUPER_ATTRIBUTE_VALUE_1]],
        );
        $otherProductViewTransfers = $productAbstractViewTransferFinder->getProductViewTransfers(
            $productAbstractIds,
            static::LOCALE_NAME,
            [static::ID_PRODUCT_ABSTRACT_1 => [static::SUPER_ATTRIBUTE_NAME => static::SUPER_ATTRIBUTE_VALUE_2]],
        );

        // Assert
        $this->assertCount(1, $productViewTransfers);
        $this->assertCount(1, $otherProductViewTransfers);
        $this->assertNotSame($productViewTransfers[0], $otherProductViewTransfers[0]);
    }

    /**
     * @param list<int> $productAbstractIds
     * @param int $expectedStorageReadCount
     *
     * @return \Spryker\Client\ProductStorage\Finder\ProductAbstractViewTransferFinder
     */
    protected function createProductAbstractViewTransferFinder(
        array $productAbstractIds,
        int $expectedStorageReadCount = 1
    ): ProductAbstractViewTransferFinder {
        $productStorageDataCollection = [];
        foreach ($productAbstractIds as $idProductAbstract) {
            $productStorageDataCollection[] = [static::KEY_ID_PRODUCT_ABSTRACT => $idProductAbstract];
        }

        $productAbstractStorageReaderMock = $this->createMock(ProductAbstractStorageReaderInterface::class);
        $productAbstractStorageReaderMock
            ->expects($this->exactly($expectedStorageReadCount))
            ->method('getBulkProductAbstractStorageDataByProductAbstractIdsForLocaleNameAndStore')
            ->willReturn($productStorageDataCollection);

        $productStorageDataMapperMock = $this->createMock(ProductStorageDataMapperInterface::class);
        $productStorageDataMapperMock
            ->method('mapProductStorageData')
            ->willReturnCallback(function ($locale, array $productStorageData): ProductViewTransfer {
                return (new ProductViewTransfer())
                    ->setIdProductAbstract($productStorageData[static::KEY_ID_PRODUCT_ABSTRACT]);
            });

        $storeClientMock = $this->createMock(ProductStorageToStoreClientInterface::class);
        $storeClientMock
            ->method('getCurrentStore')
            ->willReturn((new StoreTransfer())->setName(static::STORE_NAME_DE));

        return new ProductAbstractViewTransferFinder(
            $productAbstractStorageReaderMock,
            $productStorageDataMapperMock,
            $storeClientMock,
        );
    }
}

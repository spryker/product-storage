<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Client\ProductStorage\ProductConcreteSearch;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\AttributeMapStorageTransfer;
use Generated\Shared\Transfer\ProductConcreteCriteriaFilterTransfer;
use Generated\Shared\Transfer\ProductImageStorageTransfer;
use Generated\Shared\Transfer\ProductViewTransfer;
use PHPUnit\Framework\MockObject\MockObject;
use Spryker\Client\ProductStorage\Dependency\Client\ProductStorageToLocaleInterface;
use Spryker\Client\ProductStorage\Finder\ProductViewTransferFinderInterface;
use Spryker\Client\ProductStorage\ProductConcreteSearch\ProductConcreteStorageCatalogSearcher;
use Spryker\Client\ProductStorage\Storage\ProductConcreteStorageReaderInterface;
use SprykerTest\Client\ProductStorage\ProductStorageClientTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Client
 * @group ProductStorage
 * @group ProductConcreteSearch
 * @group ProductConcreteStorageCatalogSearcherTest
 * Add your own group annotations below this line
 */
class ProductConcreteStorageCatalogSearcherTest extends Unit
{
    protected const string LOCALE_EN = 'en_US';

    protected const int PRODUCT_ABSTRACT_ID = 100;

    protected const int PRODUCT_CONCRETE_ID = 200;

    protected const string PRODUCT_CONCRETE_SKU = 'test-concrete-sku-001';

    protected const string IMAGE_URL_SMALL = 'https://example.com/image-small.jpg';

    protected const string IMAGE_URL_LARGE = 'https://example.com/image-large.jpg';

    /**
     * @uses \Spryker\Client\Catalog\Plugin\Elasticsearch\ResultFormatter\ProductConcreteCatalogSearchResultFormatterPlugin::NAME
     */
    protected const string RESULT_FORMATTER_KEY = 'ProductConcreteCatalogSearchResultFormatterPlugin';

    protected ProductStorageClientTester $tester;

    /**
     * @return array<string, array<string, mixed>>
     */
    public function provideEarlyReturnEnrichCompletionScenarios(): array
    {
        return [
            'completion is already populated' => [
                'suggestSearchResult' => [
                    'completion' => ['existing-sku'],
                    'suggestionByType' => [],
                ],
                'expectedCompletion' => ['existing-sku'],
            ],
            'suggestionByType is empty' => [
                'suggestSearchResult' => [
                    'completion' => [],
                    'suggestionByType' => [],
                ],
                'expectedCompletion' => [],
            ],
            'product_abstract key is missing from suggestionByType' => [
                'suggestSearchResult' => [
                    'completion' => [],
                    'suggestionByType' => ['product_concrete' => [['id_product_concrete' => 1]]],
                ],
                'expectedCompletion' => [],
            ],
            'product_abstract suggestions list is empty' => [
                'suggestSearchResult' => [
                    'completion' => [],
                    'suggestionByType' => ['product_abstract' => []],
                ],
                'expectedCompletion' => [],
            ],
        ];
    }

    public function testGivenExactSkuMatchWhenSearchingProductConcretesThenReturnsMappedTransfer(): void
    {
        // Arrange
        $concreteViewTransfer = (new ProductViewTransfer())
            ->setIdProductConcrete(static::PRODUCT_CONCRETE_ID)
            ->setSku(static::PRODUCT_CONCRETE_SKU);

        $readerMock = $this->createProductConcreteStorageReaderMock();
        $readerMock->method('findProductConcreteStorageDataByMappingForCurrentLocale')
            ->willReturn(['id_product_concrete' => static::PRODUCT_CONCRETE_ID]);

        $concreteFinderMock = $this->createProductViewTransferFinderMock();
        $concreteFinderMock->method('getProductViewTransfers')
            ->with([static::PRODUCT_CONCRETE_ID], static::LOCALE_EN)
            ->willReturn([$concreteViewTransfer]);

        $searcher = $this->createSearcher(
            readerMock: $readerMock,
            concreteFinderMock: $concreteFinderMock,
        );

        // Act
        $result = $searcher->searchProductConcretes(
            (new ProductConcreteCriteriaFilterTransfer())->setSearchString(static::PRODUCT_CONCRETE_SKU),
        );

        // Assert
        $this->assertCount(1, $result[static::RESULT_FORMATTER_KEY]);
        $this->assertSame(static::PRODUCT_CONCRETE_SKU, $result[static::RESULT_FORMATTER_KEY][0]->getSku());
        // A product view without images must map to an empty images list rather than fail.
        $this->assertSame([], $result[static::RESULT_FORMATTER_KEY][0]->getImages());
    }

    public function testGivenProductViewWithImagesWhenSearchingProductConcretesThenMapsImagesToPageSearchTransfer(): void
    {
        // Arrange
        $productImageStorageTransfer = (new ProductImageStorageTransfer())
            ->setExternalUrlSmall(static::IMAGE_URL_SMALL)
            ->setExternalUrlLarge(static::IMAGE_URL_LARGE);
        $concreteViewTransfer = (new ProductViewTransfer())
            ->setIdProductConcrete(static::PRODUCT_CONCRETE_ID)
            ->setSku(static::PRODUCT_CONCRETE_SKU)
            ->addImage($productImageStorageTransfer);

        $readerMock = $this->createProductConcreteStorageReaderMock();
        $readerMock->method('findProductConcreteStorageDataByMappingForCurrentLocale')
            ->willReturn(['id_product_concrete' => static::PRODUCT_CONCRETE_ID]);

        $concreteFinderMock = $this->createProductViewTransferFinderMock();
        $concreteFinderMock->method('getProductViewTransfers')
            ->with([static::PRODUCT_CONCRETE_ID], static::LOCALE_EN)
            ->willReturn([$concreteViewTransfer]);

        $searcher = $this->createSearcher(
            readerMock: $readerMock,
            concreteFinderMock: $concreteFinderMock,
        );

        // Act
        $result = $searcher->searchProductConcretes(
            (new ProductConcreteCriteriaFilterTransfer())->setSearchString(static::PRODUCT_CONCRETE_SKU),
        );

        // Assert
        $images = $result[static::RESULT_FORMATTER_KEY][0]->getImages();
        $this->assertCount(1, $images);
        $this->assertSame(static::IMAGE_URL_SMALL, $images[0]->getExternalUrlSmall());
        $this->assertSame(static::IMAGE_URL_LARGE, $images[0]->getExternalUrlLarge());
    }

    public function testGivenAbstractSearchResultsWhenHydratingConcretesThenReturnsMappedTransfers(): void
    {
        // Arrange
        $abstractViewTransfer = $this->buildAbstractViewTransferWithConcretes([static::PRODUCT_CONCRETE_ID]);
        $concreteViewTransfer = (new ProductViewTransfer())
            ->setIdProductConcrete(static::PRODUCT_CONCRETE_ID)
            ->setIdProductAbstract(static::PRODUCT_ABSTRACT_ID)
            ->setSku(static::PRODUCT_CONCRETE_SKU);

        $abstractFinderMock = $this->createProductViewTransferFinderMock();
        $abstractFinderMock->method('getProductViewTransfers')
            ->willReturn([$abstractViewTransfer]);

        $concreteFinderMock = $this->createProductViewTransferFinderMock();
        $concreteFinderMock->method('getProductViewTransfers')
            ->with([static::PRODUCT_CONCRETE_ID], static::LOCALE_EN)
            ->willReturn([$concreteViewTransfer]);

        $searcher = $this->createSearcher(
            concreteFinderMock: $concreteFinderMock,
            abstractFinderMock: $abstractFinderMock,
        );

        // Act
        $result = $searcher->searchProductConcretesByAbstractSearchResults(
            [['id_product_abstract' => static::PRODUCT_ABSTRACT_ID]],
        );

        // Assert
        $this->assertCount(1, $result[static::RESULT_FORMATTER_KEY]);
        $this->assertSame(static::PRODUCT_CONCRETE_SKU, $result[static::RESULT_FORMATTER_KEY][0]->getSku());
        $this->assertSame(static::PRODUCT_CONCRETE_ID, $result[static::RESULT_FORMATTER_KEY][0]->getFkProduct());
    }

    public function testGivenNoSearchStringWhenLookingUpSkuThenReturnsEmptyArray(): void
    {
        // Arrange
        $searcher = $this->createSearcher();

        // Act
        $result = $searcher->searchProductConcretes(new ProductConcreteCriteriaFilterTransfer());

        // Assert
        $this->assertSame([], $result);
    }

    public function testGivenEmptyAbstractSearchResultsWhenHydratingConcretesThenReturnsEmptyArray(): void
    {
        // Arrange
        $searcher = $this->createSearcher();

        // Act
        $result = $searcher->searchProductConcretesByAbstractSearchResults([]);

        // Assert
        $this->assertSame([], $result);
    }

    public function testGivenAbstractSearchResultsWithNoProductAbstractIdWhenHydratingThenReturnsEmptyArray(): void
    {
        // Arrange — products returned but none carry id_product_abstract
        $searcher = $this->createSearcher();

        // Act
        $result = $searcher->searchProductConcretesByAbstractSearchResults(
            [['abstract_sku' => 'no-id-field']],
        );

        // Assert
        $this->assertSame([], $result);
    }

    public function testGivenProductAbstractSuggestionsWhenEnrichingThenPopulatesCompletionWithConcreteSkus(): void
    {
        // Arrange
        $abstractViewTransfer = $this->buildAbstractViewTransferWithConcretes([static::PRODUCT_CONCRETE_ID]);
        $concreteViewTransfer = (new ProductViewTransfer())->setSku(static::PRODUCT_CONCRETE_SKU);

        $abstractFinderMock = $this->createProductViewTransferFinderMock();
        $abstractFinderMock->method('getProductViewTransfers')
            ->with([static::PRODUCT_ABSTRACT_ID], static::LOCALE_EN)
            ->willReturn([$abstractViewTransfer]);

        $concreteFinderMock = $this->createProductViewTransferFinderMock();
        $concreteFinderMock->method('getProductViewTransfers')
            ->willReturn([$concreteViewTransfer]);

        $suggestSearchResult = [
            'completion' => [],
            'suggestionByType' => [
                'product_abstract' => [['id_product_abstract' => static::PRODUCT_ABSTRACT_ID]],
            ],
        ];

        $searcher = $this->createSearcher(
            concreteFinderMock: $concreteFinderMock,
            abstractFinderMock: $abstractFinderMock,
        );

        // Act
        $result = $searcher->enrichSuggestSearchResultWithCompletion($suggestSearchResult, 'test-concrete');

        // Assert
        $this->assertSame([static::PRODUCT_CONCRETE_SKU], $result['completion']);
    }

    public function testGivenConcreteSkusNotMatchingSearchStringWhenEnrichingThenFiltersThemFromCompletion(): void
    {
        // Arrange
        $abstractViewTransfer = $this->buildAbstractViewTransferWithConcretes([static::PRODUCT_CONCRETE_ID]);
        $concreteViewTransfer = (new ProductViewTransfer())->setSku(static::PRODUCT_CONCRETE_SKU);

        $abstractFinderMock = $this->createProductViewTransferFinderMock();
        $abstractFinderMock->method('getProductViewTransfers')
            ->willReturn([$abstractViewTransfer]);

        $concreteFinderMock = $this->createProductViewTransferFinderMock();
        $concreteFinderMock->method('getProductViewTransfers')
            ->willReturn([$concreteViewTransfer]);

        $suggestSearchResult = [
            'completion' => [],
            'suggestionByType' => [
                'product_abstract' => [['id_product_abstract' => static::PRODUCT_ABSTRACT_ID]],
            ],
        ];

        $searcher = $this->createSearcher(
            concreteFinderMock: $concreteFinderMock,
            abstractFinderMock: $abstractFinderMock,
        );

        // Act
        $result = $searcher->enrichSuggestSearchResultWithCompletion($suggestSearchResult, 'sony-xperia');

        // Assert
        $this->assertSame([], $result['completion']);
    }

    /**
     * @dataProvider provideEarlyReturnEnrichCompletionScenarios
     *
     * @param array<string, mixed> $suggestSearchResult
     * @param array<string> $expectedCompletion
     */
    public function testGivenSuggestResultNotRequiringEnrichmentWhenEnrichingThenReturnsCompletionUnchanged(
        array $suggestSearchResult,
        array $expectedCompletion,
    ): void {
        // Arrange
        $searcher = $this->createSearcher();

        // Act
        $result = $searcher->enrichSuggestSearchResultWithCompletion($suggestSearchResult, 'any-search-string');

        // Assert
        $this->assertSame($expectedCompletion, $result['completion']);
    }

    protected function createSearcher(
        ?ProductConcreteStorageReaderInterface $readerMock = null,
        ?ProductViewTransferFinderInterface $concreteFinderMock = null,
        ?ProductViewTransferFinderInterface $abstractFinderMock = null,
    ): ProductConcreteStorageCatalogSearcher {
        return new ProductConcreteStorageCatalogSearcher(
            productConcreteStorageReader: $readerMock ?? $this->createProductConcreteStorageReaderMock(),
            productConcreteViewTransferFinder: $concreteFinderMock ?? $this->createProductViewTransferFinderMock(),
            productAbstractViewTransferFinder: $abstractFinderMock ?? $this->createProductViewTransferFinderMock(),
            localeClient: $this->createLocaleClientMock(),
        );
    }

    protected function createProductConcreteStorageReaderMock(): ProductConcreteStorageReaderInterface&MockObject
    {
        return $this->createMock(ProductConcreteStorageReaderInterface::class);
    }

    protected function createProductViewTransferFinderMock(): ProductViewTransferFinderInterface&MockObject
    {
        return $this->createMock(ProductViewTransferFinderInterface::class);
    }

    protected function createLocaleClientMock(): ProductStorageToLocaleInterface&MockObject
    {
        $mock = $this->createMock(ProductStorageToLocaleInterface::class);
        $mock->method('getCurrentLocale')->willReturn(static::LOCALE_EN);

        return $mock;
    }

    /**
     * @param array<int> $concreteIds
     */
    protected function buildAbstractViewTransferWithConcretes(array $concreteIds): ProductViewTransfer
    {
        // productConcreteIds is a map of concrete SKU => concrete product ID
        $concreteIdMap = array_combine(
            array_map(static fn (int $id): string => sprintf('sku-%d', $id), $concreteIds),
            $concreteIds,
        );
        $attributeMap = (new AttributeMapStorageTransfer())->setProductConcreteIds($concreteIdMap);

        return (new ProductViewTransfer())
            ->setIdProductAbstract(static::PRODUCT_ABSTRACT_ID)
            ->setAttributeMap($attributeMap);
    }
}

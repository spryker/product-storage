<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\ProductStorage\ProductConcreteSearch;

use Generated\Shared\Transfer\ProductConcreteCriteriaFilterTransfer;
use Generated\Shared\Transfer\ProductConcretePageSearchTransfer;
use Generated\Shared\Transfer\ProductViewTransfer;
use Spryker\Client\ProductStorage\Dependency\Client\ProductStorageToLocaleInterface;
use Spryker\Client\ProductStorage\Finder\ProductViewTransferFinderInterface;
use Spryker\Client\ProductStorage\Storage\ProductConcreteStorageReaderInterface;

class ProductConcreteStorageCatalogSearcher implements ProductConcreteStorageCatalogSearcherInterface
{
    /**
     * @uses \Spryker\Client\Catalog\Plugin\Elasticsearch\ResultFormatter\ProductConcreteCatalogSearchResultFormatterPlugin::NAME
     */
    protected const string RESULT_FORMATTER_KEY = 'ProductConcreteCatalogSearchResultFormatterPlugin';

    protected const string MAPPING_TYPE_SKU = 'sku';

    /**
     * @uses \Spryker\Client\SearchElasticsearch\Plugin\ResultFormatter\CompletionResultFormatterPlugin::NAME
     */
    protected const string KEY_COMPLETION = 'completion';

    /**
     * @uses \Spryker\Client\SearchElasticsearch\Plugin\ResultFormatter\SuggestionByTypeResultFormatterPlugin::NAME
     */
    protected const string KEY_SUGGESTION_BY_TYPE = 'suggestionByType';

    protected const string KEY_PRODUCT_ABSTRACT = 'product_abstract';

    protected const string KEY_ID_PRODUCT_CONCRETE = 'id_product_concrete';

    protected const string KEY_ID_PRODUCT_ABSTRACT = 'id_product_abstract';

    public function __construct(
        protected ProductConcreteStorageReaderInterface $productConcreteStorageReader,
        protected ProductViewTransferFinderInterface $productConcreteViewTransferFinder,
        protected ProductViewTransferFinderInterface $productAbstractViewTransferFinder,
        protected ProductStorageToLocaleInterface $localeClient,
    ) {
    }

    /**
     * @return array<string, array<\Generated\Shared\Transfer\ProductConcretePageSearchTransfer>>
     */
    public function searchProductConcretes(ProductConcreteCriteriaFilterTransfer $productConcreteCriteriaFilterTransfer): array
    {
        $searchString = (string)$productConcreteCriteriaFilterTransfer->getSearchString();

        if (!$searchString) {
            return [];
        }

        $concreteStorageData = $this->productConcreteStorageReader->findProductConcreteStorageDataByMappingForCurrentLocale(
            static::MAPPING_TYPE_SKU,
            $searchString,
        );

        if ($concreteStorageData === null) {
            return [];
        }

        $idProductConcrete = $concreteStorageData[static::KEY_ID_PRODUCT_CONCRETE] ?? null;

        if ($idProductConcrete === null) {
            return [];
        }

        $localeName = $this->localeClient->getCurrentLocale();
        $productViewTransfers = $this->productConcreteViewTransferFinder->getProductViewTransfers([(int)$idProductConcrete], $localeName);

        return [
            static::RESULT_FORMATTER_KEY => $this->mapProductViewTransfersToProductConcretePageSearchTransfers($productViewTransfers),
        ];
    }

    /**
     * @param array<array<mixed>> $abstractSearchResults
     *
     * @return array<string, array<\Generated\Shared\Transfer\ProductConcretePageSearchTransfer>>
     */
    public function searchProductConcretesByAbstractSearchResults(array $abstractSearchResults): array
    {
        $abstractIds = $this->extractAbstractProductIds($abstractSearchResults);

        if (!$abstractIds) {
            return [];
        }

        $localeName = $this->localeClient->getCurrentLocale();
        $abstractViewTransfers = $this->productAbstractViewTransferFinder->getProductViewTransfers($abstractIds, $localeName);
        $productConcreteIds = $this->extractProductConcreteIdsFromAbstractViewTransfers($abstractViewTransfers);

        if (!$productConcreteIds) {
            return [];
        }

        $productViewTransfers = $this->productConcreteViewTransferFinder->getProductViewTransfers($productConcreteIds, $localeName);

        return [
            static::RESULT_FORMATTER_KEY => $this->mapProductViewTransfersToProductConcretePageSearchTransfers($productViewTransfers),
        ];
    }

    /**
     * @param array<string, mixed> $suggestSearchResult
     * @param string $searchString
     *
     * @return array<string, mixed>
     */
    public function enrichSuggestSearchResultWithCompletion(array $suggestSearchResult, string $searchString): array
    {
        if (!empty($suggestSearchResult[static::KEY_COMPLETION])) {
            return $suggestSearchResult;
        }

        $productAbstractSuggestions = $suggestSearchResult[static::KEY_SUGGESTION_BY_TYPE][static::KEY_PRODUCT_ABSTRACT] ?? [];

        if (!$productAbstractSuggestions) {
            return $suggestSearchResult;
        }

        $abstractIds = $this->extractAbstractProductIds($productAbstractSuggestions);

        if (!$abstractIds) {
            return $suggestSearchResult;
        }

        $localeName = $this->localeClient->getCurrentLocale();
        $abstractViewTransfers = $this->productAbstractViewTransferFinder->getProductViewTransfers($abstractIds, $localeName);
        $productConcreteIds = $this->extractProductConcreteIdsFromAbstractViewTransfers($abstractViewTransfers);

        if (!$productConcreteIds) {
            return $suggestSearchResult;
        }

        $concreteViewTransfers = $this->productConcreteViewTransferFinder->getProductViewTransfers($productConcreteIds, $localeName);
        $suggestSearchResult[static::KEY_COMPLETION] = array_values(array_filter(
            array_map(
                static fn (ProductViewTransfer $productViewTransfer): string => (string)$productViewTransfer->getSku(),
                $concreteViewTransfers,
            ),
            static fn (string $sku): bool => mb_stripos($sku, $searchString) !== false,
        ));

        return $suggestSearchResult;
    }

    /**
     * @param array<array<mixed>> $abstractSearchResults
     *
     * @return array<int>
     */
    protected function extractAbstractProductIds(array $abstractSearchResults): array
    {
        $abstractIds = [];

        foreach ($abstractSearchResults as $abstractProduct) {
            $idProductAbstract = $abstractProduct[static::KEY_ID_PRODUCT_ABSTRACT] ?? null;

            if ($idProductAbstract !== null) {
                $abstractIds[] = (int)$idProductAbstract;
            }
        }

        return array_unique($abstractIds);
    }

    /**
     * product_concrete_ids is a map of concrete SKU => concrete product ID
     *
     * @param array<\Generated\Shared\Transfer\ProductViewTransfer> $abstractViewTransfers
     *
     * @return array<int>
     */
    protected function extractProductConcreteIdsFromAbstractViewTransfers(array $abstractViewTransfers): array
    {
        $productConcreteIds = [];

        foreach ($abstractViewTransfers as $abstractViewTransfer) {
            $productConcreteIds = array_merge(
                $productConcreteIds,
                $this->getConcreteIdsFromAbstractViewTransfer($abstractViewTransfer),
            );
        }

        return array_unique($productConcreteIds);
    }

    /**
     * @param array<\Generated\Shared\Transfer\ProductViewTransfer> $productViewTransfers
     *
     * @return array<\Generated\Shared\Transfer\ProductConcretePageSearchTransfer>
     */
    protected function mapProductViewTransfersToProductConcretePageSearchTransfers(array $productViewTransfers): array
    {
        $productConcretePageSearchTransfers = [];

        foreach ($productViewTransfers as $productViewTransfer) {
            $productConcretePageSearchTransfers[] = (new ProductConcretePageSearchTransfer())
                ->setFkProduct($productViewTransfer->getIdProductConcrete())
                ->setFkProductAbstract($productViewTransfer->getIdProductAbstract())
                ->setSku($productViewTransfer->getSku())
                ->setName($productViewTransfer->getName())
                ->setImages($productViewTransfer->getImages()->getArrayCopy());
        }

        return $productConcretePageSearchTransfers;
    }

    /**
     * @return array<int>
     */
    protected function getConcreteIdsFromAbstractViewTransfer(ProductViewTransfer $abstractViewTransfer): array
    {
        $attributeMap = $abstractViewTransfer->getAttributeMap();

        if ($attributeMap === null) {
            return [];
        }

        return array_map(
            static fn (mixed $id): int => (int)$id,
            array_values($attributeMap->getProductConcreteIds()),
        );
    }
}

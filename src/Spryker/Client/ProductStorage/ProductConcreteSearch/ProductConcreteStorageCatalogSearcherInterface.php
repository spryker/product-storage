<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\ProductStorage\ProductConcreteSearch;

use Generated\Shared\Transfer\ProductConcreteCriteriaFilterTransfer;

interface ProductConcreteStorageCatalogSearcherInterface
{
    /**
     * @return array<string, array<\Generated\Shared\Transfer\ProductConcretePageSearchTransfer>>
     */
    public function searchProductConcretes(ProductConcreteCriteriaFilterTransfer $productConcreteCriteriaFilterTransfer): array;

    /**
     * @param array<array<mixed>> $abstractSearchResults
     *
     * @return array<string, array<\Generated\Shared\Transfer\ProductConcretePageSearchTransfer>>
     */
    public function searchProductConcretesByAbstractSearchResults(array $abstractSearchResults): array;

    /**
     * @param array<string, mixed> $suggestSearchResult
     * @param string $searchString
     *
     * @return array<string, mixed>
     */
    public function enrichSuggestSearchResultWithCompletion(array $suggestSearchResult, string $searchString): array;
}

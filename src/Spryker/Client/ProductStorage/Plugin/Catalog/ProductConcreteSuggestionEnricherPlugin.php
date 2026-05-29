<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\ProductStorage\Plugin\Catalog;

use Spryker\Client\CatalogExtension\Dependency\Plugin\ProductConcreteSuggestionEnricherPluginInterface;
use Spryker\Client\Kernel\AbstractPlugin;

/**
 * @method \Spryker\Client\ProductStorage\ProductStorageFactory getFactory()
 */
class ProductConcreteSuggestionEnricherPlugin extends AbstractPlugin implements ProductConcreteSuggestionEnricherPluginInterface
{
    /**
     * {@inheritDoc}
     *
     * @param array<string, mixed> $suggestSearchResult
     * @param string $searchString
     *
     * @return array<string, mixed>
     */
    public function enrichSuggestSearchResultWithCompletion(array $suggestSearchResult, string $searchString): array
    {
        return $this->getFactory()
            ->createProductConcreteStorageCatalogSearcher()
            ->enrichSuggestSearchResultWithCompletion($suggestSearchResult, $searchString);
    }
}

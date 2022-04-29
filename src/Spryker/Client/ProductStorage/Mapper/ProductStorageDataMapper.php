<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\ProductStorage\Mapper;

use Generated\Shared\Transfer\ProductStorageCriteriaTransfer;
use Generated\Shared\Transfer\ProductViewTransfer;
use Laminas\Filter\FilterChain;
use Laminas\Filter\StringToLower;
use Laminas\Filter\Word\CamelCaseToUnderscore;
use Spryker\Client\Kernel\Locator;
use Spryker\Client\ProductStorage\Dependency\Client\ProductStorageToLocaleInterface;
use Spryker\Client\ProductStorage\Filter\ProductAbstractAttributeMapRestrictionFilterInterface;
use Spryker\Client\ProductStorage\ProductStorageConfig;
use Spryker\Client\ProductStorageExtension\Dependency\Plugin\ProductViewExpanderByCriteriaPluginInterface;
use Spryker\Client\ProductStorageExtension\Dependency\Plugin\ProductViewExpanderPluginInterface;

class ProductStorageDataMapper implements ProductStorageDataMapperInterface
{
    /**
     * @var array<\Spryker\Client\ProductStorageExtension\Dependency\Plugin\ProductViewExpanderPluginInterface>
     */
    protected $productStorageExpanderPlugins;

    /**
     * @var \Spryker\Client\ProductStorage\Filter\ProductAbstractAttributeMapRestrictionFilterInterface
     */
    protected $productAbstractVariantsRestrictionFilter;

    /**
     * @var \Spryker\Client\ProductStorage\Dependency\Client\ProductStorageToLocaleInterface
     */
    protected $localeClient;

    /**
     * @uses \Spryker\Client\ProductStorage\Mapper\ProductStorageDataMapper::filterProductStorageExpanderPlugins()
     *
     * @param array<\Spryker\Client\ProductStorageExtension\Dependency\Plugin\ProductViewExpanderPluginInterface> $storageProductExpanderPlugins
     * @param \Spryker\Client\ProductStorage\Filter\ProductAbstractAttributeMapRestrictionFilterInterface $productAbstractVariantsRestrictionFilter
     * @param \Spryker\Client\ProductStorage\Dependency\Client\ProductStorageToLocaleInterface $localeClient
     */
    public function __construct(
        array $storageProductExpanderPlugins,
        ProductAbstractAttributeMapRestrictionFilterInterface $productAbstractVariantsRestrictionFilter,
        ProductStorageToLocaleInterface $localeClient
    ) {
        $this->productStorageExpanderPlugins = array_filter($storageProductExpanderPlugins, [$this, 'filterProductStorageExpanderPlugins']);
        $this->productAbstractVariantsRestrictionFilter = $productAbstractVariantsRestrictionFilter;
        $this->localeClient = $localeClient;
    }

    /**
     * @param string $locale
     * @param array $productStorageData
     * @param array $selectedAttributes
     * @param \Generated\Shared\Transfer\ProductStorageCriteriaTransfer|null $productStorageCriteriaTransfer
     *
     * @return \Generated\Shared\Transfer\ProductViewTransfer
     */
    public function mapProductStorageData(
        $locale,
        array $productStorageData,
        array $selectedAttributes = [],
        ?ProductStorageCriteriaTransfer $productStorageCriteriaTransfer = null
    ) {
        $productStorageData = $this->filterAbstractProductVariantsData($productStorageData);
        $productViewTransfer = $this->createProductViewTransfer($productStorageData);
        $productViewTransfer->setSelectedAttributes($selectedAttributes);

        foreach ($this->productStorageExpanderPlugins as $productViewExpanderPlugin) {
            if ($productViewExpanderPlugin instanceof ProductViewExpanderByCriteriaPluginInterface) {
                $productViewTransfer = $productViewExpanderPlugin->expandProductViewTransfer($productViewTransfer, $productStorageData, $locale, $productStorageCriteriaTransfer);

                continue;
            }

            $productViewTransfer = $productViewExpanderPlugin->expandProductViewTransfer($productViewTransfer, $productStorageData, $locale);
        }

        return $productViewTransfer;
    }

    /**
     * @param array $productStorageData
     *
     * @return array
     */
    protected function filterAbstractProductVariantsData(array $productStorageData): array
    {
        return $this->productAbstractVariantsRestrictionFilter->filterAbstractProductVariantsData($productStorageData);
    }

    /**
     * @param array $productStorageData
     *
     * @return \Generated\Shared\Transfer\ProductViewTransfer
     */
    protected function createProductViewTransfer(array $productStorageData)
    {
        if (ProductStorageConfig::isCollectorCompatibilityMode()) {
            return $this->formatCollectorData($productStorageData);
        }

        $productStorageTransfer = new ProductViewTransfer();
        $productStorageTransfer->fromArray($productStorageData, true);

        return $productStorageTransfer;
    }

    /**
     * @param array $productStorageData
     *
     * @return \Generated\Shared\Transfer\ProductViewTransfer
     */
    private function formatCollectorData(array $productStorageData): ProductViewTransfer
    {
        unset($productStorageData['prices'], $productStorageData['categories'], $productStorageData['imageSets']);
        $productStorageData = $this->changeKeys($productStorageData);

        $clientLocatorClass = Locator::class;
        /** @var \Generated\Zed\Ide\AutoCompletion&\Spryker\Shared\Kernel\LocatorLocatorInterface $locator */
        $locator = $clientLocatorClass::getInstance();
        $productClient = $locator->product()->client();

        $attributeMap = $productClient->getAttributeMapByIdAndLocale($productStorageData['id_product_abstract'], $this->localeClient->getCurrentLocale());
        $attributeMap = $this->changeKeys($attributeMap);

        $productStorageData['attribute_map'] = $attributeMap;

        $productStorageTransfer = new ProductViewTransfer();
        $productStorageTransfer->fromArray($productStorageData, true);

        return $productStorageTransfer;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array
     */
    private function changeKeys(array $data): array
    {
        $filterChain = new FilterChain();
        $filterChain
            ->attach(new CamelCaseToUnderscore())
            ->attach(new StringToLower());

        $filteredData = [];

        foreach ($data as $key => $value) {
            $filteredData[$filterChain->filter($key)] = $value;
        }

        return $filteredData;
    }

    /**
     * @param \Spryker\Client\ProductStorageExtension\Dependency\Plugin\ProductViewExpanderPluginInterface $storageProductExpanderPlugin
     *
     * @return bool
     */
    protected function filterProductStorageExpanderPlugins(ProductViewExpanderPluginInterface $storageProductExpanderPlugin): bool
    {
        return true;
    }
}

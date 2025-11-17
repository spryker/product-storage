<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductStorage\Business\Provider;

use ArrayObject;
use Generated\Shared\Transfer\ProductAbstractReadinessRequestTransfer;
use Generated\Shared\Transfer\ProductReadinessTransfer;
use Spryker\Zed\ProductStorage\Dependency\Facade\ProductStorageToStoreFacadeInterface;
use Spryker\Zed\ProductStorage\Persistence\ProductStorageRepositoryInterface;

class StorageTableProductAbstractReadinessProvider implements ProductAbstractReadinessProviderInterface
{
    /**
     * @var string
     */
    protected const TITLE_IN_STORAGE = 'In Storage table for store/locale';

    /**
     * @var string
     */
    protected const KEY_LOCALE = 'Locale';

    /**
     * @var string
     */
    protected const KEY_LOCALE_NAME = 'locale_name';

    /**
     * @var string
     */
    protected const KEY_SPY_PRODUCT_ABSTRACT = 'SpyProductAbstract';

    /**
     * @var string
     */
    protected const KEY_SPY_PRODUCT_ABSTRACT_STORES = 'SpyProductAbstractStores';

    /**
     * @var string
     */
    protected const KEY_SPY_STORE = 'SpyStore';

    /**
     * @var string
     */
    protected const KEY_NAME = 'name';

    /**
     * @var string
     */
    protected const FALLBACK_VALUE_NO_LOCALES = '-';

    /**
     * @var string
     */
    protected const FALLBACK_VALUE_NO_STORES = '-';

    /**
     * @var string
     */
    protected const FORMAT_LOCALE_SEPARATOR = ', ';

    /**
     * @var string
     */
    protected const FORMAT_STORE_LOCALE_SEPARATOR = ' | ';

    /**
     * @var string
     */
    protected const FORMAT_STORE_LOCALE = '%s: %s';

    /**
     * @var \Spryker\Zed\ProductStorage\Persistence\ProductStorageRepositoryInterface
     */
    protected $productStorageRepository;

    /**
     * @var \Spryker\Zed\ProductStorage\Dependency\Facade\ProductStorageToStoreFacadeInterface
     */
    protected $storeFacade;

    /**
     * @param \Spryker\Zed\ProductStorage\Persistence\ProductStorageRepositoryInterface $productStorageRepository
     * @param \Spryker\Zed\ProductStorage\Dependency\Facade\ProductStorageToStoreFacadeInterface $storeFacade
     */
    public function __construct(
        ProductStorageRepositoryInterface $productStorageRepository,
        ProductStorageToStoreFacadeInterface $storeFacade
    ) {
        $this->productStorageRepository = $productStorageRepository;
        $this->storeFacade = $storeFacade;
    }

    /**
     * @param \Generated\Shared\Transfer\ProductAbstractReadinessRequestTransfer $productAbstractReadinessRequestTransfer
     * @param \ArrayObject<int, \Generated\Shared\Transfer\ProductReadinessTransfer> $productReadinessTransfers
     *
     * @return \ArrayObject<int, \Generated\Shared\Transfer\ProductReadinessTransfer>
     */
    public function provide(
        ProductAbstractReadinessRequestTransfer $productAbstractReadinessRequestTransfer,
        ArrayObject $productReadinessTransfers
    ): ArrayObject {
        $idProductAbstract = $productAbstractReadinessRequestTransfer->getProductAbstract()->getIdProductAbstract();
        $productAbstractStorageData = $this->productStorageRepository->getProductAbstractsByIds([$idProductAbstract]);

        $storeLocaleMap = $this->buildStoreLocaleMap($productAbstractStorageData);
        $values = $this->formatStoreLocaleCombinations($storeLocaleMap);

        $productReadinessTransfers->append(
            (new ProductReadinessTransfer())
                ->setTitle(static::TITLE_IN_STORAGE)
                ->setValues($values),
        );

        return $productReadinessTransfers;
    }

    /**
     * @param array<mixed> $productAbstractStorageData
     *
     * @return array<string, array<string>>
     */
    protected function buildStoreLocaleMap(array $productAbstractStorageData): array
    {
        $storeLocaleMap = [];

        foreach ($productAbstractStorageData as $row) {
            $localeName = $this->extractLocaleName($row);
            $stores = $this->extractStores($row);

            foreach ($stores as $storeData) {
                $storeName = $this->extractStoreName($storeData);
                $storeLocaleMap[$storeName][] = $localeName;
            }
        }

        return $this->removeDuplicateLocales($storeLocaleMap);
    }

    /**
     * @param array<string, array<string>> $storeLocaleMap
     *
     * @return array<string>
     */
    protected function formatStoreLocaleCombinations(array $storeLocaleMap): array
    {
        $formattedParts = [];

        foreach ($this->storeFacade->getAllStores() as $storeTransfer) {
            $storeName = $storeTransfer->getName();
            $locales = $storeLocaleMap[$storeName] ?? [];

            $formattedParts[] = $this->formatStoreLocales($storeName, $locales);
        }

        return (bool)$formattedParts
            ? [implode(static::FORMAT_STORE_LOCALE_SEPARATOR, $formattedParts)]
            : [static::FALLBACK_VALUE_NO_STORES];
    }

    /**
     * @param array<string, array<string>> $storeLocaleMap
     *
     * @return array<string, array<string>>
     */
    protected function removeDuplicateLocales(array $storeLocaleMap): array
    {
        foreach ($storeLocaleMap as $storeName => $locales) {
            $uniqueLocales = array_unique($locales);
            sort($uniqueLocales);
            $storeLocaleMap[$storeName] = $uniqueLocales;
        }

        return $storeLocaleMap;
    }

    /**
     * @param array<mixed> $row
     *
     * @return string
     */
    protected function extractLocaleName(array $row): string
    {
        return $row[static::KEY_LOCALE][static::KEY_LOCALE_NAME];
    }

    /**
     * @param array<mixed> $row
     *
     * @return array<mixed>
     */
    protected function extractStores(array $row): array
    {
        return $row[static::KEY_SPY_PRODUCT_ABSTRACT][static::KEY_SPY_PRODUCT_ABSTRACT_STORES] ?? [];
    }

    /**
     * @param array<mixed> $storeData
     *
     * @return string
     */
    protected function extractStoreName(array $storeData): string
    {
        return $storeData[static::KEY_SPY_STORE][static::KEY_NAME];
    }

    /**
     * @param string $storeName
     * @param array<string> $locales
     *
     * @return string
     */
    protected function formatStoreLocales(string $storeName, array $locales): string
    {
        if (!$locales) {
            return sprintf(static::FORMAT_STORE_LOCALE, $storeName, static::FALLBACK_VALUE_NO_LOCALES);
        }

        $localeList = implode(static::FORMAT_LOCALE_SEPARATOR, $locales);

        return sprintf(static::FORMAT_STORE_LOCALE, $storeName, $localeList);
    }
}

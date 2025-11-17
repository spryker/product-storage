<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductStorage\Business\Provider;

use ArrayObject;
use Generated\Shared\Transfer\ProductAbstractReadinessRequestTransfer;
use Generated\Shared\Transfer\ProductReadinessTransfer;
use Spryker\Client\ProductStorage\ProductStorageClientInterface;
use Spryker\Zed\ProductStorage\Dependency\Facade\ProductStorageToStoreFacadeInterface;

class StorageProductAbstractReadinessProvider implements ProductAbstractReadinessProviderInterface
{
    /**
     * @var string
     */
    protected const TITLE_IN_STORAGE = 'In Storage for store/locale';

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
     * @var \Spryker\Client\ProductStorage\ProductStorageClientInterface
     */
    protected $productStorageClient;

    /**
     * @var \Spryker\Zed\ProductStorage\Dependency\Facade\ProductStorageToStoreFacadeInterface
     */
    protected $storeFacade;

    /**
     * @param \Spryker\Client\ProductStorage\ProductStorageClientInterface $productStorageClient
     * @param \Spryker\Zed\ProductStorage\Dependency\Facade\ProductStorageToStoreFacadeInterface $storeFacade
     */
    public function __construct(
        ProductStorageClientInterface $productStorageClient,
        ProductStorageToStoreFacadeInterface $storeFacade
    ) {
        $this->productStorageClient = $productStorageClient;
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

        $storeLocaleMap = $this->buildStoreLocaleMap($idProductAbstract);
        $values = $this->formatStoreLocaleCombinations($storeLocaleMap);

        $productReadinessTransfers->append(
            (new ProductReadinessTransfer())
                ->setTitle(static::TITLE_IN_STORAGE)
                ->setValues($values),
        );

        return $productReadinessTransfers;
    }

    /**
     * @param int $idProductAbstract
     *
     * @return array<string, array<string>>
     */
    protected function buildStoreLocaleMap(int $idProductAbstract): array
    {
        $storeLocaleMap = [];
        $stores = $this->storeFacade->getAllStores();

        foreach ($stores as $storeTransfer) {
            $locales = $this->findAvailableLocalesForStore($idProductAbstract, $storeTransfer);

            if ($locales) {
                $storeLocaleMap[$storeTransfer->getName()] = $locales;
            }
        }

        return $storeLocaleMap;
    }

    /**
     * @param int $idProductAbstract
     * @param \Generated\Shared\Transfer\StoreTransfer $storeTransfer
     *
     * @return array<string>
     */
    protected function findAvailableLocalesForStore(int $idProductAbstract, $storeTransfer): array
    {
        $locales = $storeTransfer->getAvailableLocaleIsoCodes();

        if (!$locales) {
            return [];
        }

        $availableLocales = [];
        foreach ($locales as $localeIsoCode) {
            if ($this->hasProductDataInStorage($idProductAbstract, $localeIsoCode, $storeTransfer->getName())) {
                $availableLocales[] = $localeIsoCode;
            }
        }

        sort($availableLocales);

        return $availableLocales;
    }

    /**
     * @param int $idProductAbstract
     * @param string $localeIsoCode
     * @param string $storeName
     *
     * @return bool
     */
    protected function hasProductDataInStorage(int $idProductAbstract, string $localeIsoCode, string $storeName): bool
    {
        $productData = $this->productStorageClient->getBulkProductAbstractStorageDataByProductAbstractIdsForLocaleNameAndStore(
            [$idProductAbstract],
            $localeIsoCode,
            $storeName,
        );

        return (bool)$productData;
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

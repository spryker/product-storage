<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductStorage\Business\Provider;

use ArrayObject;
use Generated\Shared\Transfer\ProductConcreteReadinessRequestTransfer;
use Generated\Shared\Transfer\ProductReadinessTransfer;
use Spryker\Client\ProductStorage\ProductStorageClientInterface;
use Spryker\Zed\ProductStorage\Dependency\Facade\ProductStorageToStoreFacadeInterface;

class StorageProductConcreteReadinessProvider implements ProductConcreteReadinessProviderInterface
{
    /**
     * @var string
     */
    protected const TITLE_IN_STORAGE = 'In Storage for locale';

    /**
     * @var string
     */
    protected const FALLBACK_VALUE_NO_LOCALES = '-';

    /**
     * @var string
     */
    protected const FORMAT_LOCALE_SEPARATOR = ', ';

    /**
     * @var \Spryker\Client\ProductStorage\ProductStorageClientInterface
     */
    protected ProductStorageClientInterface $productStorageClient;

    /**
     * @var \Spryker\Zed\ProductStorage\Dependency\Facade\ProductStorageToStoreFacadeInterface
     */
    protected ProductStorageToStoreFacadeInterface $storeFacade;

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
     * @param \Generated\Shared\Transfer\ProductConcreteReadinessRequestTransfer $productConcreteReadinessRequestTransfer
     * @param \ArrayObject<int, \Generated\Shared\Transfer\ProductReadinessTransfer> $productReadinessTransfers
     *
     * @return \ArrayObject<int, \Generated\Shared\Transfer\ProductReadinessTransfer>
     */
    public function provide(
        ProductConcreteReadinessRequestTransfer $productConcreteReadinessRequestTransfer,
        ArrayObject $productReadinessTransfers
    ): ArrayObject {
        $idProductConcrete = $productConcreteReadinessRequestTransfer->getProductConcrete()->getIdProductConcrete();

        $localeNames = $this->findAvailableLocales($idProductConcrete);
        $values = $this->formatLocales($localeNames);

        $productReadinessTransfers->append(
            (new ProductReadinessTransfer())
                ->setTitle(static::TITLE_IN_STORAGE)
                ->setValues($values),
        );

        return $productReadinessTransfers;
    }

    /**
     * @param int $idProductConcrete
     *
     * @return array<string>
     */
    protected function findAvailableLocales(int $idProductConcrete): array
    {
        $allLocales = $this->getAllLocalesFromStores();
        $availableLocales = [];

        foreach ($allLocales as $localeName) {
            if ($this->hasProductDataInStorage($idProductConcrete, $localeName)) {
                $availableLocales[] = $localeName;
            }
        }

        return $availableLocales;
    }

    /**
     * @return array<string>
     */
    protected function getAllLocalesFromStores(): array
    {
        $allLocales = [];
        $stores = $this->storeFacade->getAllStores();

        foreach ($stores as $storeTransfer) {
            $locales = $storeTransfer->getAvailableLocaleIsoCodes();

            if ($locales) {
                $allLocales = array_merge($allLocales, $locales);
            }
        }

        return array_unique($allLocales);
    }

    /**
     * @param int $idProductConcrete
     * @param string $localeIsoCode
     *
     * @return bool
     */
    protected function hasProductDataInStorage(int $idProductConcrete, string $localeIsoCode): bool
    {
        $productData = $this->productStorageClient->getBulkProductConcreteStorageData(
            [$idProductConcrete],
            $localeIsoCode,
        );

        return (bool)$productData;
    }

    /**
     * @param array<string> $localeNames
     *
     * @return array<string>
     */
    protected function formatLocales(array $localeNames): array
    {
        if (!$localeNames) {
            return [static::FALLBACK_VALUE_NO_LOCALES];
        }

        return [implode(static::FORMAT_LOCALE_SEPARATOR, $localeNames)];
    }
}

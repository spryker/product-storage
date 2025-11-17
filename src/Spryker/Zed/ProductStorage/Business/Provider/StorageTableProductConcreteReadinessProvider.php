<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductStorage\Business\Provider;

use ArrayObject;
use Generated\Shared\Transfer\ProductConcreteReadinessRequestTransfer;
use Generated\Shared\Transfer\ProductReadinessTransfer;
use Spryker\Zed\ProductStorage\Dependency\Facade\ProductStorageToStoreFacadeInterface;
use Spryker\Zed\ProductStorage\Persistence\ProductStorageRepositoryInterface;

class StorageTableProductConcreteReadinessProvider implements ProductConcreteReadinessProviderInterface
{
    /**
     * @var string
     */
    protected const TITLE_IN_STORAGE = 'In Storage table for locale';

    /**
     * @var string
     */
    protected const KEY_LOCALE = 'locale';

    /**
     * @var string
     */
    protected const FALLBACK_VALUE_NO_LOCALES = '-';

    /**
     * @var string
     */
    protected const FORMAT_LOCALE_SEPARATOR = ', ';

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
        $productConcreteStorageData = $this->productStorageRepository->getProductConcretesByIds([$idProductConcrete]);

        $localeNames = $this->extractLocaleNames($productConcreteStorageData);
        $values = $this->formatLocales($localeNames);

        $productReadinessTransfers->append(
            (new ProductReadinessTransfer())
                ->setTitle(static::TITLE_IN_STORAGE)
                ->setValues($values),
        );

        return $productReadinessTransfers;
    }

    /**
     * @param array<array<string, mixed>> $productConcreteStorageData
     *
     * @return array<string>
     */
    protected function extractLocaleNames(array $productConcreteStorageData): array
    {
        $localeNames = [];

        foreach ($productConcreteStorageData as $productConcreteStorageArray) {
            $localeName = $productConcreteStorageArray[static::KEY_LOCALE] ?? null;

            if ($localeName !== null) {
                $localeNames[] = $localeName;
            }
        }

        $uniqueLocales = array_unique($localeNames);
        sort($uniqueLocales);

        return $uniqueLocales;
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

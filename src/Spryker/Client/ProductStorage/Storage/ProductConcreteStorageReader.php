<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\ProductStorage\Storage;

use Generated\Shared\Transfer\ProductConcreteStorageTransfer;
use Generated\Shared\Transfer\SynchronizationDataTransfer;
use Laminas\Filter\FilterChain;
use Laminas\Filter\StringToLower;
use Laminas\Filter\Word\CamelCaseToUnderscore;
use Spryker\Client\Kernel\Locator;
use Spryker\Client\ProductStorage\Dependency\Client\ProductStorageToLocaleInterface;
use Spryker\Client\ProductStorage\Dependency\Client\ProductStorageToStorageClientInterface;
use Spryker\Client\ProductStorage\Dependency\Service\ProductStorageToSynchronizationServiceInterface;
use Spryker\Client\ProductStorage\Dependency\Service\ProductStorageToUtilEncodingServiceInterface;
use Spryker\Client\ProductStorage\Exception\ProductConcreteDataCacheNotFoundException;
use Spryker\Client\ProductStorage\ProductStorageConfig;
use Spryker\Service\Synchronization\Dependency\Plugin\SynchronizationKeyGeneratorPluginInterface;
use Spryker\Shared\ProductStorage\ProductStorageConstants;

class ProductConcreteStorageReader implements ProductConcreteStorageReaderInterface
{
    /**
     * @var string
     */
    protected const KEY_ID_PRODUCT_CONCRETE = 'id_product_concrete';

    /**
     * @var string
     */
    protected const KEY_PRICES = 'prices';

    /**
     * @var string
     */
    protected const KEY_IMAGE_SETS = 'imageSets';

    /**
     * @var string
     */
    protected const KEY_ID = 'id';

    /**
     * @uses \Spryker\Zed\Storage\Communication\Table\StorageTable::KV_PREFIX
     *
     * @var string
     */
    protected const KV_PREFIX = 'kv:';

    /**
     * @var \Spryker\Client\ProductStorage\Dependency\Service\ProductStorageToSynchronizationServiceInterface
     */
    protected $synchronizationService;

    /**
     * @var \Spryker\Client\ProductStorage\Dependency\Client\ProductStorageToStorageClientInterface
     */
    protected $storageClient;

    /**
     * @var \Spryker\Client\ProductStorage\Dependency\Client\ProductStorageToLocaleInterface
     */
    protected $localeClient;

    /**
     * @var \Spryker\Client\ProductStorage\Dependency\Service\ProductStorageToUtilEncodingServiceInterface
     */
    protected $utilEncodingService;

    /**
     * @var array<\Spryker\Client\ProductStorageExtension\Dependency\Plugin\ProductConcreteRestrictionPluginInterface>
     */
    protected $productConcreteRestrictionPlugins;

    /**
     * @var array<\Spryker\Client\ProductStorageExtension\Dependency\Plugin\ProductConcreteRestrictionFilterPluginInterface>
     */
    protected $productConcreteRestrictionFilterPlugins;

    /**
     * @var array
     */
    protected static $productsConcreteDataCache = [];

    /**
     * @var \Spryker\Service\Synchronization\Dependency\Plugin\SynchronizationKeyGeneratorPluginInterface|null
     */
    protected static $storageKeyBuilder;

    /**
     * @param \Spryker\Client\ProductStorage\Dependency\Client\ProductStorageToStorageClientInterface $storageClient
     * @param \Spryker\Client\ProductStorage\Dependency\Service\ProductStorageToSynchronizationServiceInterface $synchronizationService
     * @param \Spryker\Client\ProductStorage\Dependency\Client\ProductStorageToLocaleInterface $localeClient
     * @param \Spryker\Client\ProductStorage\Dependency\Service\ProductStorageToUtilEncodingServiceInterface $utilEncodingService
     * @param array<\Spryker\Client\ProductStorageExtension\Dependency\Plugin\ProductConcreteRestrictionPluginInterface> $productConcreteRestrictionPlugins
     * @param array<\Spryker\Client\ProductStorageExtension\Dependency\Plugin\ProductConcreteRestrictionFilterPluginInterface> $productConcreteRestrictionFilterPlugins
     */
    public function __construct(
        ProductStorageToStorageClientInterface $storageClient,
        ProductStorageToSynchronizationServiceInterface $synchronizationService,
        ProductStorageToLocaleInterface $localeClient,
        ProductStorageToUtilEncodingServiceInterface $utilEncodingService,
        array $productConcreteRestrictionPlugins = [],
        array $productConcreteRestrictionFilterPlugins = []
    ) {
        $this->storageClient = $storageClient;
        $this->synchronizationService = $synchronizationService;
        $this->localeClient = $localeClient;
        $this->utilEncodingService = $utilEncodingService;
        $this->productConcreteRestrictionPlugins = $productConcreteRestrictionPlugins;
        $this->productConcreteRestrictionFilterPlugins = $productConcreteRestrictionFilterPlugins;
    }

    /**
     * @deprecated Use {@link \Spryker\Client\ProductStorage\Storage\ProductConcreteStorageReader::findProductConcreteStorageData()} instead.
     *
     * @param int $idProductConcrete
     * @param string $localeName
     *
     * @return array
     */
    public function getProductConcreteStorageData($idProductConcrete, $localeName)
    {
        return $this->findProductConcreteStorageData($idProductConcrete, $localeName);
    }

    /**
     * @param int $idProductConcrete
     * @param string $localeName
     *
     * @return array|null
     */
    public function findProductConcreteStorageData($idProductConcrete, $localeName): ?array
    {
        if (!$idProductConcrete) {
            return null;
        }

        if ($this->hasProductConcreteDataCacheByIdProductConcreteAndLocaleName($idProductConcrete, $localeName)) {
            return $this->getProductConcreteDataCacheByIdProductConcreteAndLocaleName($idProductConcrete, $localeName);
        }

        $productConcreteData = $this->findStorageData($idProductConcrete, $localeName);
        $this->cacheProductConcreteDataByIdProductConcreteAndLocaleName($idProductConcrete, $localeName, $productConcreteData);

        return $productConcreteData;
    }

    /**
     * @param string $mappingType
     * @param array<string> $identifiers
     * @param string $localeName
     *
     * @return array<int>
     */
    public function getProductConcreteIdsByMapping(string $mappingType, array $identifiers, string $localeName): array
    {
        $storageKeys = $this->getStorageKeysByMapping($mappingType, $identifiers, $localeName);
        $mappingData = array_filter($this->storageClient->getMulti($storageKeys));
        if (count($mappingData) === 0) {
            return [];
        }

        return $this->getFilteredProductConcreteIds($storageKeys, $mappingData);
    }

    /**
     * @param array<string> $storageKeys
     * @param array $mappingData
     *
     * @return array<int>
     */
    protected function getFilteredProductConcreteIds(array $storageKeys, array $mappingData): array
    {
        $identifiersByStorageKey = [];
        foreach ($storageKeys as $identifier => $storageKey) {
            $identifiersByStorageKey[static::KV_PREFIX . $storageKey] = $identifier;
        }

        $productConcreteIds = [];
        foreach ($mappingData as $storageKey => $mappingDataItem) {
            $decodedMappingDataItem = $this->utilEncodingService->decodeJson($mappingDataItem, true);
            if (!$decodedMappingDataItem || !$decodedMappingDataItem[static::KEY_ID]) {
                continue;
            }

            $productConcreteIds[$identifiersByStorageKey[$storageKey]] = $decodedMappingDataItem[static::KEY_ID];
        }

        return $this->filterRestrictedProductConcreteIds($productConcreteIds);
    }

    /**
     * @param string $mappingType
     * @param array<string> $identifiers
     * @param string $localeName
     *
     * @return array<string>
     */
    protected function getStorageKeysByMapping(string $mappingType, array $identifiers, string $localeName): array
    {
        $storageKeys = [];
        foreach ($identifiers as $identifier) {
            $storageKeys[$identifier] = $this->getStorageKey(
                sprintf('%s:%s', $mappingType, $identifier),
                $localeName,
            );
        }

        return $storageKeys;
    }

    /**
     * @param int $idProductConcrete
     * @param string $localeName
     *
     * @return array|null
     */
    protected function findStorageData(int $idProductConcrete, string $localeName): ?array
    {
        if ($this->isProductConcreteRestricted($idProductConcrete)) {
            return null;
        }

        if (ProductStorageConfig::isCollectorCompatibilityMode()) {
            $clientLocatorClass = Locator::class;
            /** @var \Generated\Zed\Ide\AutoCompletion&\Spryker\Shared\Kernel\LocatorLocatorInterface $locator */
            $locator = $clientLocatorClass::getInstance();
            $productClient = $locator->product()->client();
            $collectorData = $productClient->getProductConcreteByIdAndLocale($idProductConcrete, $localeName);

            unset($collectorData['prices'], $collectorData['imageSets']);
            $collectorData = $this->changeKeys($collectorData);

            return $collectorData;
        }

        $key = $this->getStorageKey((string)$idProductConcrete, $localeName);

        return $this->storageClient->get($key);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array
     */
    protected function changeKeys(array $data): array
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
     * @param array<int> $productIds
     *
     * @return array<\Generated\Shared\Transfer\ProductConcreteStorageTransfer>
     */
    public function getProductConcreteStorageTransfersForCurrentLocale(array $productIds): array
    {
        return $this->getProductConcreteStorageTransfers($productIds, $this->localeClient->getCurrentLocale());
    }

    /**
     * @param array<int> $productIds
     * @param string $localeName
     *
     * @return array<\Generated\Shared\Transfer\ProductConcreteStorageTransfer>
     */
    protected function getProductConcreteStorageTransfers(array $productIds, string $localeName): array
    {
        $productConcreteStorageTransfers = [];
        foreach ($productIds as $productId) {
            $productConcreteStorageData = $this->findProductConcreteStorageData($productId, $localeName);
            if ($productConcreteStorageData === null) {
                continue;
            }

            $productConcreteStorageTransfers[] = $this->mapProductConcreteStorageDataToTransfer($productConcreteStorageData);
        }

        return $productConcreteStorageTransfers;
    }

    /**
     * @param array $productConcreteStorageData
     *
     * @return \Generated\Shared\Transfer\ProductConcreteStorageTransfer
     */
    protected function mapProductConcreteStorageDataToTransfer(array $productConcreteStorageData): ProductConcreteStorageTransfer
    {
        return (new ProductConcreteStorageTransfer())
            ->fromArray($productConcreteStorageData, true);
    }

    /**
     * @param int $idProductConcrete
     *
     * @return bool
     */
    public function isProductConcreteRestricted(int $idProductConcrete): bool
    {
        foreach ($this->productConcreteRestrictionPlugins as $productConcreteRestrictionPlugin) {
            if ($productConcreteRestrictionPlugin->isRestricted($idProductConcrete)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $mappingType
     * @param string $identifier
     * @param string $localeName
     *
     * @return array|null
     */
    public function findProductConcreteStorageDataByMapping(string $mappingType, string $identifier, string $localeName): ?array
    {
        $reference = $mappingType . ':' . $identifier;
        $mappingKey = $this->getStorageKey($reference, $localeName);
        $mappingData = $this->storageClient->get($mappingKey);

        if (!$mappingData) {
            return null;
        }

        return $this->findProductConcreteStorageData($mappingData[static::KEY_ID], $localeName);
    }

    /**
     * @param string $mappingType
     * @param array<string> $identifiers
     * @param string $localeName
     *
     * @return array
     */
    public function getBulkProductConcreteStorageDataByMapping(string $mappingType, array $identifiers, string $localeName): array
    {
        $storageKeys = $this->generateMappingStorageKeys($mappingType, $identifiers, $localeName);
        $mappings = $this->storageClient->getMulti($storageKeys);
        $productConcreteIds = [];
        foreach ($mappings as $mapping) {
            $decodedMapping = $this->utilEncodingService->decodeJson($mapping, true);
            if ($decodedMapping) {
                $productConcreteIds[] = $decodedMapping[static::KEY_ID];
            }
        }

        if (!$productConcreteIds) {
            return [];
        }

        return $this->getBulkProductConcreteStorageDataByProductConcreteIdsAndLocaleName($productConcreteIds, $localeName);
    }

    /**
     * @param string $mappingType
     * @param string $identifier
     *
     * @return array|null
     */
    public function findProductConcreteStorageDataByMappingForCurrentLocale(string $mappingType, string $identifier): ?array
    {
        return $this->findProductConcreteStorageDataByMapping(
            $mappingType,
            $identifier,
            $this->localeClient->getCurrentLocale(),
        );
    }

    /**
     * @param string $reference
     * @param string $localeName
     *
     * @return string
     */
    protected function getStorageKey(string $reference, string $localeName): string
    {
        $synchronizationDataTransfer = new SynchronizationDataTransfer();
        $synchronizationDataTransfer
            ->setReference($reference)
            ->setLocale($localeName);

        return $this->getStorageKeyBuilder()->generateKey($synchronizationDataTransfer);
    }

    /**
     * @return \Spryker\Service\Synchronization\Dependency\Plugin\SynchronizationKeyGeneratorPluginInterface
     */
    protected function getStorageKeyBuilder(): SynchronizationKeyGeneratorPluginInterface
    {
        if (static::$storageKeyBuilder === null) {
            static::$storageKeyBuilder = $this->synchronizationService->getStorageKeyBuilder(ProductStorageConstants::PRODUCT_CONCRETE_RESOURCE_NAME);
        }

        return static::$storageKeyBuilder;
    }

    /**
     * @param int $idProductConcrete
     * @param string $localeName
     *
     * @throws \Spryker\Client\ProductStorage\Exception\ProductConcreteDataCacheNotFoundException
     *
     * @return array
     */
    protected function getProductConcreteDataCacheByIdProductConcreteAndLocaleName(int $idProductConcrete, string $localeName): array
    {
        if (!$this->hasProductConcreteDataCacheByIdProductConcreteAndLocaleName($idProductConcrete, $localeName)) {
            throw new ProductConcreteDataCacheNotFoundException();
        }

        return static::$productsConcreteDataCache[$idProductConcrete][$localeName];
    }

    /**
     * @param int $idProductConcrete
     * @param string $localeName
     *
     * @return bool
     */
    protected function hasProductConcreteDataCacheByIdProductConcreteAndLocaleName(int $idProductConcrete, string $localeName): bool
    {
        return isset(static::$productsConcreteDataCache[$idProductConcrete][$localeName]);
    }

    /**
     * @param int $idProductConcrete
     * @param string $localeName
     * @param array|null $productData
     *
     * @return void
     */
    protected function cacheProductConcreteDataByIdProductConcreteAndLocaleName(int $idProductConcrete, string $localeName, ?array $productData): void
    {
        static::$productsConcreteDataCache[$idProductConcrete][$localeName] = $productData;
    }

    /**
     * @param array<int> $productConcreteIds
     * @param string $localeName
     *
     * @return array
     */
    protected function getProductConcreteDataCacheByProductConcreteIdsAndLocaleName(array $productConcreteIds, string $localeName): array
    {
        $cachedProductConcreteData = [];
        foreach ($productConcreteIds as $idProductConcrete) {
            if ($this->hasProductConcreteDataCacheByIdProductConcreteAndLocaleName($idProductConcrete, $localeName)) {
                $cachedProductConcreteData[$idProductConcrete] = $this->getProductConcreteDataCacheByIdProductConcreteAndLocaleName($idProductConcrete, $localeName);
            }
        }

        return $cachedProductConcreteData;
    }

    /**
     * @param array<int> $productConcreteIds
     * @param string $localeName
     *
     * @return array
     */
    public function getBulkProductConcreteStorageDataByProductConcreteIdsAndLocaleName(array $productConcreteIds, string $localeName): array
    {
        $cachedProductConcreteData = $this->getProductConcreteDataCacheByProductConcreteIdsAndLocaleName($productConcreteIds, $localeName);

        $productConcreteIds = array_diff($productConcreteIds, array_keys($cachedProductConcreteData));
        $productConcreteIds = $this->filterRestrictedProductConcreteIds($productConcreteIds);
        if (!$productConcreteIds) {
            return $cachedProductConcreteData;
        }

        $productConcreteStorageData = $this->getBulkProductConcreteStorageData($productConcreteIds, $localeName);

        return array_merge($cachedProductConcreteData, $productConcreteStorageData);
    }

    /**
     * @param array<int> $productConcreteIds
     * @param string $localeName
     *
     * @return array
     */
    protected function getBulkProductConcreteStorageData(array $productConcreteIds, string $localeName): array
    {
        if (ProductStorageConfig::isCollectorCompatibilityMode()) {
            return $this->getBulkProductConcreteStorageDataForCollectorCompatibilityMode($productConcreteIds, $localeName);
        }

        $productStorageDataCollection = $this->storageClient->getMulti($this->generateStorageKeys($productConcreteIds, $localeName));
        $productStorageDataCollection = array_filter($productStorageDataCollection);

        return $this->mapBulkProductConcreteStorageData($productStorageDataCollection, $localeName);
    }

    /**
     * @param array<int> $productConcreteIds
     * @param string $localeName
     *
     * @return array<string>
     */
    protected function generateStorageKeys(array $productConcreteIds, string $localeName): array
    {
        $storageKeys = [];
        foreach ($productConcreteIds as $idProductConcrete) {
            $storageKeys[] = $this->getStorageKey((string)$idProductConcrete, $localeName);
        }

        return $storageKeys;
    }

    /**
     * @param string $mappingType
     * @param array<string> $identifiers
     * @param string $localeName
     *
     * @return array<string>
     */
    protected function generateMappingStorageKeys(string $mappingType, array $identifiers, string $localeName): array
    {
        $mappingKeys = [];
        foreach ($identifiers as $identifier) {
            $mappingKeys[] = $this->getStorageKey($mappingType . ':' . $identifier, $localeName);
        }

        return $mappingKeys;
    }

    /**
     * @param array $productStorageDataCollection
     * @param string $localeName
     *
     * @return array
     */
    protected function mapBulkProductConcreteStorageData(array $productStorageDataCollection, string $localeName): array
    {
        $productConcreteStorageData = [];
        foreach ($productStorageDataCollection as $productStorageData) {
            $productStorageData = $this->utilEncodingService->decodeJson($productStorageData, true);
            $idProductConcrete = $productStorageData[static::KEY_ID_PRODUCT_CONCRETE];
            $productConcreteStorageData[$idProductConcrete] = $productStorageData;

            $this->cacheProductConcreteDataByIdProductConcreteAndLocaleName($idProductConcrete, $localeName, $productStorageData);
        }

        return $productConcreteStorageData;
    }

    /**
     * @param array<int> $productConcreteIds
     * @param string $localeName
     *
     * @return array
     */
    protected function getBulkProductConcreteStorageDataForCollectorCompatibilityMode(array $productConcreteIds, string $localeName): array
    {
        $clientLocatorClass = Locator::class;
        /** @var \Generated\Zed\Ide\AutoCompletion&\Spryker\Shared\Kernel\LocatorLocatorInterface $locator */
        $locator = $clientLocatorClass::getInstance();
        $productClient = $locator->product()->client();

        $collectorData = [];
        foreach ($productConcreteIds as $idProductConcrete) {
            $productConcreteData = $productClient->getProductConcreteByIdAndLocale($idProductConcrete, $localeName);

            unset($productConcreteData[static::KEY_PRICES], $productConcreteData[static::KEY_IMAGE_SETS]);
            $productConcreteData = $this->changeKeys($productConcreteData);

            $collectorData[$productConcreteData[static::KEY_ID_PRODUCT_CONCRETE]] = $productConcreteData;
        }

        return $collectorData;
    }

    /**
     * @param array<int> $productConcreteIds
     *
     * @return array<int>
     */
    public function filterRestrictedProductConcreteIds(array $productConcreteIds): array
    {
        if (!$productConcreteIds) {
            return [];
        }

        //This was added for BC reason (if no bulk plugins were added)
        if (!$this->productConcreteRestrictionFilterPlugins) {
            $filteredIds = [];
            foreach ($productConcreteIds as $idProductConcrete) {
                if (!$this->isProductConcreteRestricted($idProductConcrete)) {
                    $filteredIds[] = $idProductConcrete;
                }
            }

            return $filteredIds;
        }

        foreach ($this->productConcreteRestrictionFilterPlugins as $productConcreteRestrictionFilterPlugin) {
            $productConcreteIds = $productConcreteRestrictionFilterPlugin->filter($productConcreteIds);
        }

        return $productConcreteIds;
    }
}

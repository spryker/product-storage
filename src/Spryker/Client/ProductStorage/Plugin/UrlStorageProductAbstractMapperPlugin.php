<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\ProductStorage\Plugin;

use Generated\Shared\Transfer\SynchronizationDataTransfer;
use Generated\Shared\Transfer\UrlStorageResourceMapTransfer;
use Generated\Shared\Transfer\UrlStorageTransfer;
use Spryker\Client\Kernel\AbstractPlugin;
use Spryker\Client\ProductStorage\ProductStorageConfig;
use Spryker\Client\UrlStorage\Dependency\Plugin\UrlStorageResourceMapperPluginInterface;
use Spryker\Service\Synchronization\Dependency\Plugin\SynchronizationKeyGeneratorPluginInterface;
use Spryker\Shared\ProductStorage\ProductStorageConfig as SharedProductStorageConfig;
use Spryker\Shared\ProductStorage\ProductStorageConstants;

/**
 * @method \Spryker\Client\ProductStorage\ProductStorageFactory getFactory()
 */
class UrlStorageProductAbstractMapperPlugin extends AbstractPlugin implements UrlStorageResourceMapperPluginInterface
{
    /**
     * @var \Spryker\Service\Synchronization\Dependency\Plugin\SynchronizationKeyGeneratorPluginInterface|null
     */
    protected static $storageKeyBuilder;

    /**
     * @var string|null
     */
    protected static $storeName;

    /**
     * @param \Generated\Shared\Transfer\UrlStorageTransfer $urlStorageTransfer
     * @param array<string, mixed> $options
     *
     * @return \Generated\Shared\Transfer\UrlStorageResourceMapTransfer
     */
    public function map(UrlStorageTransfer $urlStorageTransfer, array $options = [])
    {
        $urlStorageResourceMapTransfer = new UrlStorageResourceMapTransfer();
        $idProductAbstract = $urlStorageTransfer->getFkResourceProductAbstract();

        if (!$idProductAbstract) {
            return $urlStorageResourceMapTransfer;
        }

        $resourceKey = $this->generateKey($idProductAbstract, $options['locale']);

        /** @var \Spryker\Client\ProductStorage\ProductStorageConfig $config */
        $config = $this->getFactory()->getConfig();

        if ($config->isProductAbstractStorageUnifiedEnabled()) {
            $storageData = $this->getFactory()->getStorageClient()->get($resourceKey);

            if ($storageData !== null && !$this->isCurrentStoreInStorageData($storageData)) {
                return $urlStorageResourceMapTransfer;
            }

            if ($storageData === null && $config->isProductAbstractStorageUnifiedFallbackEnabled()) {
                $resourceKey = $this->generateFallbackKey($idProductAbstract, $options['locale']);
            }
        }

        $urlStorageResourceMapTransfer->setResourceKey($resourceKey);
        $urlStorageResourceMapTransfer->setType(ProductStorageConstants::PRODUCT_ABSTRACT_RESOURCE_NAME);

        return $urlStorageResourceMapTransfer;
    }

    /**
     * @param int $idProductAbstract
     * @param string $locale
     *
     * @return string
     */
    protected function generateKey($idProductAbstract, $locale)
    {
        if (ProductStorageConfig::isCollectorCompatibilityMode()) {
            $collectorDataKey = sprintf(
                '%s.%s.resource.product_abstract.%s',
                strtolower($this->getStoreName()),
                strtolower($locale),
                $idProductAbstract,
            );

            return $collectorDataKey;
        }
        $synchronizationDataTransfer = new SynchronizationDataTransfer();
        $synchronizationDataTransfer->setStore($this->getStoreName());
        $synchronizationDataTransfer->setLocale($locale);
        $synchronizationDataTransfer->setReference($idProductAbstract);

        return $this->getStorageKeyBuilder()->generateKey($synchronizationDataTransfer);
    }

    protected function generateFallbackKey(int $idProductAbstract, string $locale): string
    {
        if (ProductStorageConfig::isCollectorCompatibilityMode()) {
            return sprintf(
                '%s.%s.resource.product_abstract.%s',
                strtolower($this->getActualStoreName()),
                strtolower($locale),
                $idProductAbstract,
            );
        }

        $synchronizationDataTransfer = new SynchronizationDataTransfer();
        $synchronizationDataTransfer->setStore($this->getActualStoreName());
        $synchronizationDataTransfer->setLocale($locale);
        $synchronizationDataTransfer->setReference($idProductAbstract);

        return $this->getStorageKeyBuilder()->generateKey($synchronizationDataTransfer);
    }

    /**
     * @param array<string, mixed> $storageData
     */
    protected function isCurrentStoreInStorageData(array $storageData): bool
    {
        if (!isset($storageData[SharedProductStorageConfig::PRODUCT_ABSTRACT_STORES_MAP])) {
            return true;
        }

        return in_array($this->getActualStoreName(), $storageData[SharedProductStorageConfig::PRODUCT_ABSTRACT_STORES_MAP], true);
    }

    protected function getStoreName(): string
    {
        /** @var \Spryker\Client\ProductStorage\ProductStorageConfig $config */
        $config = $this->getFactory()->getConfig();
        if ($config->isProductAbstractStorageUnifiedEnabled()) {
            return SharedProductStorageConfig::PRODUCT_ABSTRACT_STORAGE_UNIFIED_STORE_KEY;
        }

        return $this->getActualStoreName();
    }

    protected function getActualStoreName(): string
    {
        if (static::$storeName !== null) {
            return static::$storeName;
        }

        static::$storeName = $this->getFactory()
            ->getStoreClient()
            ->getCurrentStore()
            ->getNameOrFail();

        return static::$storeName;
    }

    protected function getStorageKeyBuilder(): SynchronizationKeyGeneratorPluginInterface
    {
        if (static::$storageKeyBuilder === null) {
            static::$storageKeyBuilder = $this->getFactory()
                ->getSynchronizationService()
                ->getStorageKeyBuilder(ProductStorageConstants::PRODUCT_ABSTRACT_RESOURCE_NAME);
        }

        return static::$storageKeyBuilder;
    }
}

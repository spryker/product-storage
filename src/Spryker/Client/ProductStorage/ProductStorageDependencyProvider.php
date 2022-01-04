<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\ProductStorage;

use Spryker\Client\Kernel\AbstractDependencyProvider;
use Spryker\Client\Kernel\Container;
use Spryker\Client\ProductStorage\Dependency\Client\ProductStorageToLocaleBridge;
use Spryker\Client\ProductStorage\Dependency\Client\ProductStorageToStorageClientBridge;
use Spryker\Client\ProductStorage\Dependency\Client\ProductStorageToStoreClientBridge;
use Spryker\Client\ProductStorage\Dependency\Service\ProductStorageToSynchronizationServiceBridge;
use Spryker\Client\ProductStorage\Dependency\Service\ProductStorageToUtilEncodingServiceBridge;
use Spryker\Client\ProductStorage\Dependency\Service\ProductStorageToUtilSanitizeServiceBridge;

/**
 * @method \Spryker\Client\ProductStorage\ProductStorageConfig getConfig()
 */
class ProductStorageDependencyProvider extends AbstractDependencyProvider
{
    /**
     * @var string
     */
    public const CLIENT_LOCALE = 'CLIENT_LOCALE';

    /**
     * @var string
     */
    public const CLIENT_STORAGE = 'CLIENT_STORAGE';

    /**
     * @var string
     */
    public const CLIENT_STORE = 'CLIENT_STORE';

    /**
     * @var string
     */
    public const SERVICE_SYNCHRONIZATION = 'SERVICE_SYNCHRONIZATION';

    /**
     * @var string
     */
    public const SERVICE_UTIL_ENCODING = 'SERVICE_UTIL_ENCODING';

    /**
     * @var string
     */
    public const SERVICE_UTIL_SANITIZE = 'SERVICE_UTIL_SANITIZE';

    /**
     * @var string
     */
    public const PLUGIN_PRODUCT_VIEW_EXPANDERS = 'PLUGIN_STORAGE_PRODUCT_EXPANDERS';

    /**
     * @var string
     */
    public const PLUGINS_PRODUCT_ABSTRACT_RESTRICTION = 'PLUGINS_PRODUCT_ABSTRACT_RESTRICTION';

    /**
     * @var string
     */
    public const PLUGINS_PRODUCT_CONCRETE_RESTRICTION = 'PLUGINS_PRODUCT_CONCRETE_RESTRICTION';

    /**
     * @var string
     */
    public const PLUGINS_PRODUCT_CONCRETE_EXPANDER = 'PLUGINS_PRODUCT_CONCRETE_EXPANDER';

    /**
     * @var string
     */
    public const PLUGINS_PRODUCT_ABSTRACT_RESTRICTION_FILTER = 'PLUGINS_PRODUCT_ABSTRACT_RESTRICTION_FILTER';

    /**
     * @var string
     */
    public const PLUGINS_PRODUCT_CONCRETE_RESTRICTION_FILTER = 'PLUGINS_PRODUCT_CONCRETE_RESTRICTION_FILTER';

    /**
     * @param \Spryker\Client\Kernel\Container $container
     *
     * @return \Spryker\Client\Kernel\Container
     */
    public function provideServiceLayerDependencies(Container $container)
    {
        $container = parent::provideServiceLayerDependencies($container);
        $container = $this->addStorageClient($container);
        $container = $this->addSynchronizationService($container);
        $container = $this->addUtilEncodingService($container);
        $container = $this->addLocaleClient($container);
        $container = $this->addProductViewExpanderPlugins($container);
        $container = $this->addProductAbstractRestrictionPlugins($container);
        $container = $this->addProductConcreteRestrictionPlugins($container);
        $container = $this->addProductConcreteExpanderPlugins($container);
        $container = $this->addProductAbstractRestrictionFilterPlugins($container);
        $container = $this->addProductConcreteRestrictionFilterPlugins($container);
        $container = $this->addStoreClient($container);
        $container = $this->addUtilSanitizeService($container);

        return $container;
    }

    /**
     * @param \Spryker\Client\Kernel\Container $container
     *
     * @return \Spryker\Client\Kernel\Container
     */
    protected function addStoreClient(Container $container): Container
    {
        $container->set(static::CLIENT_STORE, function (Container $container) {
            return new ProductStorageToStoreClientBridge(
                $container->getLocator()->store()->client(),
            );
        });

        return $container;
    }

    /**
     * @param \Spryker\Client\Kernel\Container $container
     *
     * @return \Spryker\Client\Kernel\Container
     */
    protected function addStorageClient(Container $container)
    {
        $container->set(static::CLIENT_STORAGE, function (Container $container) {
            return new ProductStorageToStorageClientBridge($container->getLocator()->storage()->client());
        });

        return $container;
    }

    /**
     * @param \Spryker\Client\Kernel\Container $container
     *
     * @return \Spryker\Client\Kernel\Container
     */
    protected function addSynchronizationService(Container $container)
    {
        $container->set(static::SERVICE_SYNCHRONIZATION, function (Container $container) {
            return new ProductStorageToSynchronizationServiceBridge($container->getLocator()->synchronization()->service());
        });

        return $container;
    }

    /**
     * @param \Spryker\Client\Kernel\Container $container
     *
     * @return \Spryker\Client\Kernel\Container
     */
    protected function addUtilEncodingService(Container $container): Container
    {
        $container->set(static::SERVICE_UTIL_ENCODING, function (Container $container) {
            return new ProductStorageToUtilEncodingServiceBridge(
                $container->getLocator()->utilEncoding()->service(),
            );
        });

        return $container;
    }

    /**
     * @param \Spryker\Client\Kernel\Container $container
     *
     * @return \Spryker\Client\Kernel\Container
     */
    protected function addUtilSanitizeService(Container $container): Container
    {
        $container->set(static::SERVICE_UTIL_SANITIZE, function (Container $container) {
            return new ProductStorageToUtilSanitizeServiceBridge(
                $container->getLocator()->utilSanitize()->service(),
            );
        });

        return $container;
    }

    /**
     * @param \Spryker\Client\Kernel\Container $container
     *
     * @return \Spryker\Client\Kernel\Container
     */
    protected function addLocaleClient(Container $container)
    {
        $container->set(static::CLIENT_LOCALE, function (Container $container) {
            return new ProductStorageToLocaleBridge($container->getLocator()->locale()->client());
        });

        return $container;
    }

    /**
     * @param \Spryker\Client\Kernel\Container $container
     *
     * @return \Spryker\Client\Kernel\Container
     */
    protected function addProductViewExpanderPlugins(Container $container)
    {
        $container->set(static::PLUGIN_PRODUCT_VIEW_EXPANDERS, function () {
            return $this->getProductViewExpanderPlugins();
        });

        return $container;
    }

    /**
     * @param \Spryker\Client\Kernel\Container $container
     *
     * @return \Spryker\Client\Kernel\Container
     */
    protected function addProductAbstractRestrictionPlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_PRODUCT_ABSTRACT_RESTRICTION, function () {
            return $this->getProductAbstractRestrictionPlugins();
        });

        return $container;
    }

    /**
     * @param \Spryker\Client\Kernel\Container $container
     *
     * @return \Spryker\Client\Kernel\Container
     */
    protected function addProductConcreteRestrictionPlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_PRODUCT_CONCRETE_RESTRICTION, function () {
            return $this->getProductConcreteRestrictionPlugins();
        });

        return $container;
    }

    /**
     * @param \Spryker\Client\Kernel\Container $container
     *
     * @return \Spryker\Client\Kernel\Container
     */
    protected function addProductConcreteExpanderPlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_PRODUCT_CONCRETE_EXPANDER, function () {
            return $this->getProductConcreteExpanderPlugins();
        });

        return $container;
    }

    /**
     * @param \Spryker\Client\Kernel\Container $container
     *
     * @return \Spryker\Client\Kernel\Container
     */
    protected function addProductAbstractRestrictionFilterPlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_PRODUCT_ABSTRACT_RESTRICTION_FILTER, function () {
            return $this->getProductAbstractRestrictionFilterPlugins();
        });

        return $container;
    }

    /**
     * @param \Spryker\Client\Kernel\Container $container
     *
     * @return \Spryker\Client\Kernel\Container
     */
    protected function addProductConcreteRestrictionFilterPlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_PRODUCT_CONCRETE_RESTRICTION_FILTER, function () {
            return $this->getProductConcreteRestrictionFilterPlugins();
        });

        return $container;
    }

    /**
     * @return array<\Spryker\Client\ProductStorage\Dependency\Plugin\ProductViewExpanderPluginInterface>
     */
    protected function getProductViewExpanderPlugins()
    {
        return [];
    }

    /**
     * @return array<\Spryker\Client\ProductStorageExtension\Dependency\Plugin\ProductAbstractRestrictionPluginInterface>
     */
    protected function getProductAbstractRestrictionPlugins(): array
    {
        return [];
    }

    /**
     * @return array<\Spryker\Client\ProductStorageExtension\Dependency\Plugin\ProductConcreteRestrictionPluginInterface>
     */
    protected function getProductConcreteRestrictionPlugins(): array
    {
        return [];
    }

    /**
     * @return array<\Spryker\Client\ProductStorageExtension\Dependency\Plugin\ProductConcreteExpanderPluginInterface>
     */
    protected function getProductConcreteExpanderPlugins(): array
    {
        return [];
    }

    /**
     * @return array<\Spryker\Client\ProductStorageExtension\Dependency\Plugin\ProductAbstractRestrictionFilterPluginInterface>
     */
    protected function getProductAbstractRestrictionFilterPlugins(): array
    {
        return [];
    }

    /**
     * @return array<\Spryker\Client\ProductStorageExtension\Dependency\Plugin\ProductConcreteRestrictionFilterPluginInterface>
     */
    protected function getProductConcreteRestrictionFilterPlugins(): array
    {
        return [];
    }
}

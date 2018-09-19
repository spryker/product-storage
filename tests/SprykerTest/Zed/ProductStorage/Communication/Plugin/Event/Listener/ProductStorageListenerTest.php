<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\ProductStorage\Communication\Plugin\Event\Listener;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\EventEntityTransfer;
use Generated\Shared\Transfer\ProductAbstractTransfer;
use Generated\Shared\Transfer\StoreRelationTransfer;
use Orm\Zed\Product\Persistence\Map\SpyProductAbstractLocalizedAttributesTableMap;
use Orm\Zed\Product\Persistence\Map\SpyProductLocalizedAttributesTableMap;
use Orm\Zed\Product\Persistence\Map\SpyProductTableMap;
use Orm\Zed\ProductStorage\Persistence\SpyProductAbstractStorageQuery;
use Orm\Zed\ProductStorage\Persistence\SpyProductConcreteStorageQuery;
use Orm\Zed\Url\Persistence\Map\SpyUrlTableMap;
use Orm\Zed\Url\Persistence\SpyUrlQuery;
use PHPUnit\Framework\SkippedTestError;
use Spryker\Client\Kernel\Container;
use Spryker\Client\Queue\QueueDependencyProvider;
use Spryker\Shared\Config\Config;
use Spryker\Shared\PropelQueryBuilder\PropelQueryBuilderConstants;
use Spryker\Zed\Product\Dependency\ProductEvents;
use Spryker\Zed\ProductStorage\Business\ProductStorageBusinessFactory;
use Spryker\Zed\ProductStorage\Business\ProductStorageFacade;
use Spryker\Zed\ProductStorage\Communication\Plugin\Event\Listener\ProductAbstractLocalizedAttributesStorageListener;
use Spryker\Zed\ProductStorage\Communication\Plugin\Event\Listener\ProductAbstractStorageListener;
use Spryker\Zed\ProductStorage\Communication\Plugin\Event\Listener\ProductAbstractUrlStorageListener;
use Spryker\Zed\ProductStorage\Communication\Plugin\Event\Listener\ProductConcreteLocalizedAttributesStorageListener;
use Spryker\Zed\ProductStorage\Communication\Plugin\Event\Listener\ProductConcreteProductAbstractLocalizedAttributesStorageListener;
use Spryker\Zed\ProductStorage\Communication\Plugin\Event\Listener\ProductConcreteProductAbstractRelationStorageListener;
use Spryker\Zed\ProductStorage\Communication\Plugin\Event\Listener\ProductConcreteProductAbstractStorageListener;
use Spryker\Zed\ProductStorage\Communication\Plugin\Event\Listener\ProductConcreteProductAbstractUrlStorageListener;
use Spryker\Zed\ProductStorage\Communication\Plugin\Event\Listener\ProductConcreteStorageListener;
use Spryker\Zed\Store\Business\StoreFacadeInterface;
use Spryker\Zed\Url\Dependency\UrlEvents;
use SprykerTest\Zed\ProductStorage\ProductStorageConfigMock;

/**
 * Auto-generated group annotations
 * @group SprykerTest
 * @group Zed
 * @group ProductStorage
 * @group Communication
 * @group Plugin
 * @group Event
 * @group Listener
 * @group ProductStorageListenerTest
 * Add your own group annotations below this line
 */
class ProductStorageListenerTest extends Unit
{
    const NUMBER_OF_STORES = 3;
    const NUMBER_OF_LOCALES = 1;

    /**
     * @var \SprykerTest\Zed\ProductStorage\ProductStorageCommunicationTester
     */
    protected $tester;

    /**
     * @var \Generated\Shared\Transfer\ProductConcreteTransfer
     */
    protected $productConcreteTransfer;

    /**
     * @var \Generated\Shared\Transfer\ProductAbstractTransfer
     */
    protected $productAbstractTransfer;

    /**
     * @throws \PHPUnit\Framework\SkippedTestError
     *
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();

        if (!$this->tester->isSuiteProject()) {
            throw new SkippedTestError('Warning: not in suite environment');
        }

        $dbEngine = Config::get(PropelQueryBuilderConstants::ZED_DB_ENGINE);
        if ($dbEngine !== 'pgsql') {
            throw new SkippedTestError('Warning: no PostgreSQL is detected');
        }

        $this->tester->setDependency(QueueDependencyProvider::QUEUE_ADAPTERS, function (Container $container) {
            return [
                $container->getLocator()->rabbitMq()->client()->createQueueAdapter(),
            ];
        });

        $this->productConcreteTransfer = $this->tester->haveProduct();
        $this->productAbstractTransfer = $this->tester->getProductFacade()->findProductAbstractById(
            $this->productConcreteTransfer->getFkProductAbstract()
        );

        $localizedAttributes = $this->tester->generateLocalizedAttributes();

        $this->tester->addLocalizedAttributesToProductAbstract($this->productAbstractTransfer, $localizedAttributes);
        $this->addStoreRelationToProductAbstracts($this->productAbstractTransfer);
        $this->tester->addLocalizedAttributesToProductConcrete($this->productConcreteTransfer, $localizedAttributes);
    }

    /**
     * @return void
     */
    public function testProductAbstractStorageListenerStoreData()
    {
        SpyProductAbstractStorageQuery::create()->filterByFkProductAbstract($this->productAbstractTransfer->getIdProductAbstract())->delete();
        $beforeCount = SpyProductAbstractStorageQuery::create()->count();

        $productAbstractStorageListener = new ProductAbstractStorageListener();
        $productAbstractStorageListener->setFacade($this->getProductStorageFacade());

        $eventTransfers = [
            (new EventEntityTransfer())->setId($this->productAbstractTransfer->getIdProductAbstract()),
        ];
        $productAbstractStorageListener->handleBulk($eventTransfers, ProductEvents::PRODUCT_ABSTRACT_PUBLISH);

        // Assert
        $this->assertProductAbstractStorage($beforeCount);
    }

    /**
     * @return void
     */
    public function testProductAbstractUrlStorageListenerStoreData()
    {
        SpyProductAbstractStorageQuery::create()->filterByFkProductAbstract($this->productAbstractTransfer->getIdProductAbstract())->delete();
        $beforeCount = SpyProductAbstractStorageQuery::create()->count();

        $productAbstractUrlStorageListener = new ProductAbstractUrlStorageListener();
        $productAbstractUrlStorageListener->setFacade($this->getProductStorageFacade());

        $eventTransfers = [
            (new EventEntityTransfer())->setForeignKeys([
                SpyUrlTableMap::COL_FK_RESOURCE_PRODUCT_ABSTRACT => $this->productAbstractTransfer->getIdProductAbstract(),
            ])
            ->setModifiedColumns([SpyUrlTableMap::COL_URL]),
        ];
        $productAbstractUrlStorageListener->handleBulk($eventTransfers, UrlEvents::ENTITY_SPY_URL_CREATE);

        // Assert
        $this->assertProductAbstractStorage($beforeCount);
    }

    /**
     * @return void
     */
    public function testProductAbstractLocalizedAttributesStorageListenerStoreData()
    {
        SpyProductAbstractStorageQuery::create()->filterByFkProductAbstract($this->productAbstractTransfer->getIdProductAbstract())->delete();
        $beforeCount = SpyProductAbstractStorageQuery::create()->count();

        $productAbstractLocalizedAttributesStorageListener = new ProductAbstractLocalizedAttributesStorageListener();
        $productAbstractLocalizedAttributesStorageListener->setFacade($this->getProductStorageFacade());

        $eventTransfers = [
            (new EventEntityTransfer())->setForeignKeys([
                SpyProductAbstractLocalizedAttributesTableMap::COL_FK_PRODUCT_ABSTRACT => $this->productAbstractTransfer->getIdProductAbstract(),
            ]),
        ];
        $productAbstractLocalizedAttributesStorageListener->handleBulk($eventTransfers, ProductEvents::ENTITY_SPY_PRODUCT_ABSTRACT_LOCALIZED_ATTRIBUTES_UPDATE);

        // Assert
        $this->assertProductAbstractStorage($beforeCount);
    }

    /**
     * @return void
     */
    public function testProductConcreteProductAbstractRelationStorageListenerStoreData()
    {
        SpyProductAbstractStorageQuery::create()->filterByFkProductAbstract($this->productAbstractTransfer->getIdProductAbstract())->delete();
        $beforeCount = SpyProductAbstractStorageQuery::create()->count();

        $productConcreteProductAbstractRelationStorageListener = new ProductConcreteProductAbstractRelationStorageListener();
        $productConcreteProductAbstractRelationStorageListener->setFacade($this->getProductStorageFacade());

        $eventTransfers = [
            (new EventEntityTransfer())->setForeignKeys([
                SpyProductTableMap::COL_FK_PRODUCT_ABSTRACT => $this->productAbstractTransfer->getIdProductAbstract(),
            ]),
        ];
        $productConcreteProductAbstractRelationStorageListener->handleBulk($eventTransfers, ProductEvents::ENTITY_SPY_PRODUCT_CREATE);

        // Assert
        $this->assertProductAbstractStorage($beforeCount);
    }

    /**
     * @return void
     */
    public function testProductConcreteStorageListenerStoreData()
    {
        SpyProductConcreteStorageQuery::create()->filterByFkProduct($this->productConcreteTransfer->getIdProductConcrete())->delete();
        $beforeCount = SpyProductConcreteStorageQuery::create()->count();

        $productConcreteStorageListener = new ProductConcreteStorageListener();
        $productConcreteStorageListener->setFacade($this->getProductStorageFacade());

        $eventTransfers = [
            (new EventEntityTransfer())->setId($this->productConcreteTransfer->getIdProductConcrete()),
        ];
        $productConcreteStorageListener->handleBulk($eventTransfers, ProductEvents::PRODUCT_CONCRETE_PUBLISH);

        // Assert
        $this->assertProductConcreteStorage($beforeCount);
    }

    /**
     * @return void
     */
    public function testProductConcreteRelationUrlStorageListenerStoreData()
    {
        SpyProductConcreteStorageQuery::create()->filterByFkProduct($this->productConcreteTransfer->getIdProductConcrete())->delete();
        $beforeCount = SpyProductConcreteStorageQuery::create()->count();

        $productConcreteProductAbstractUrlStorageListener = new ProductConcreteProductAbstractUrlStorageListener();
        $productConcreteProductAbstractUrlStorageListener->setFacade($this->getProductStorageFacade());

        $eventTransfers = [
            (new EventEntityTransfer())->setForeignKeys([
                SpyUrlTableMap::COL_FK_RESOURCE_PRODUCT_ABSTRACT => $this->productAbstractTransfer->getIdProductAbstract(),
            ])
                ->setModifiedColumns([SpyUrlTableMap::COL_URL]),
        ];
        $productConcreteProductAbstractUrlStorageListener->handleBulk($eventTransfers, UrlEvents::ENTITY_SPY_URL_CREATE);

        // Assert
        $this->assertProductConcreteStorage($beforeCount);
    }

    /**
     * @return void
     */
    public function testProductConcreteProductAbstractStorageListenerStoreData()
    {
        SpyProductConcreteStorageQuery::create()->filterByFkProduct($this->productConcreteTransfer->getIdProductConcrete())->delete();
        $beforeCount = SpyProductConcreteStorageQuery::create()->count();

        $productConcreteProductAbstractStorageListener = new ProductConcreteProductAbstractStorageListener();
        $productConcreteProductAbstractStorageListener->setFacade($this->getProductStorageFacade());

        $eventTransfers = [
            (new EventEntityTransfer())->setId($this->productAbstractTransfer->getIdProductAbstract()),
        ];
        $productConcreteProductAbstractStorageListener->handleBulk($eventTransfers, ProductEvents::ENTITY_SPY_PRODUCT_ABSTRACT_UPDATE);

        // Assert
        $this->assertProductConcreteStorage($beforeCount);
    }

    /**
     * @return void
     */
    public function testProductConcreteProductAbstractLocalizedAttributesStorageListenerStoreData()
    {
        SpyProductConcreteStorageQuery::create()->filterByFkProduct($this->productConcreteTransfer->getIdProductConcrete())->delete();
        $beforeCount = SpyProductConcreteStorageQuery::create()->count();

        $productConcreteProductAbstractLocalizedAttributesStorageListener = new ProductConcreteProductAbstractLocalizedAttributesStorageListener();
        $productConcreteProductAbstractLocalizedAttributesStorageListener->setFacade($this->getProductStorageFacade());

        $eventTransfers = [
            (new EventEntityTransfer())->setForeignKeys([
                SpyProductAbstractLocalizedAttributesTableMap::COL_FK_PRODUCT_ABSTRACT => $this->productAbstractTransfer->getIdProductAbstract(),
            ]),
        ];
        $productConcreteProductAbstractLocalizedAttributesStorageListener->handleBulk($eventTransfers, ProductEvents::ENTITY_SPY_PRODUCT_ABSTRACT_LOCALIZED_ATTRIBUTES_UPDATE);

        // Assert
        $this->assertProductConcreteStorage($beforeCount);
    }

    /**
     * @return void
     */
    public function testProductConcreteLocalizedAttributesStorageListenerStoreData()
    {
        SpyProductConcreteStorageQuery::create()->filterByFkProduct($this->productConcreteTransfer->getIdProductConcrete())->delete();
        $beforeCount = SpyProductConcreteStorageQuery::create()->count();

        $productConcreteLocalizedAttributesStorageListener = new ProductConcreteLocalizedAttributesStorageListener();
        $productConcreteLocalizedAttributesStorageListener->setFacade($this->getProductStorageFacade());

        $eventTransfers = [
            (new EventEntityTransfer())->setForeignKeys([
                SpyProductLocalizedAttributesTableMap::COL_FK_PRODUCT => $this->productConcreteTransfer->getIdProductConcrete(),
            ]),
        ];
        $productConcreteLocalizedAttributesStorageListener->handleBulk($eventTransfers, ProductEvents::ENTITY_SPY_PRODUCT_LOCALIZED_ATTRIBUTES_UPDATE);

        // Assert
        $this->assertProductConcreteStorage($beforeCount);
    }

    /**
     * @return \Spryker\Zed\ProductStorage\Business\ProductStorageFacade
     */
    protected function getProductStorageFacade()
    {
        $factory = new ProductStorageBusinessFactory();
        $factory->setConfig(new ProductStorageConfigMock());

        $facade = new ProductStorageFacade();
        $facade->setFactory($factory);

        return $facade;
    }

    /**
     * @param int $beforeCount
     *
     * @return void
     */
    protected function assertProductAbstractStorage($beforeCount)
    {
        $afterCount = SpyProductAbstractStorageQuery::create()->count();
        $this->assertSame($beforeCount + static::NUMBER_OF_LOCALES * static::NUMBER_OF_STORES, $afterCount);
        $spyProductAbstractStorage = SpyProductAbstractStorageQuery::create()
            ->orderByIdProductAbstractStorage()
            ->findOneByFkProductAbstract($this->productAbstractTransfer->getIdProductAbstract());

        $urlCollectionEntity = SpyUrlQuery::create()
            ->orderByFkResourceProductAbstract()
            ->findByFkResourceProductAbstract($this->productAbstractTransfer->getIdProductAbstract());

        $this->assertNotNull($urlCollectionEntity);
        $this->assertNotNull($urlCollectionEntity->count());

        $this->assertNotNull($spyProductAbstractStorage);
        $data = $spyProductAbstractStorage->getData();
        $this->assertSame($this->productAbstractTransfer->getSku(), $data['sku']);
        $this->assertSame(1, count($data['attributes']));
        $this->assertSame($urlCollectionEntity->getLast()->getUrl(), $data['url']);
    }

    /**
     * @param int $beforeCount
     *
     * @return void
     */
    protected function assertProductConcreteStorage($beforeCount)
    {
        $afterCount = SpyProductConcreteStorageQuery::create()->count();
        $this->assertSame($beforeCount + static::NUMBER_OF_LOCALES, $afterCount);
        $spyProductConcreteStorage = SpyProductConcreteStorageQuery::create()
            ->orderByIdProductConcreteStorage()
            ->findOneByFkProduct($this->productConcreteTransfer->getIdProductConcrete());

        $urlCollectionEntity = SpyUrlQuery::create()
            ->orderByFkResourceProductAbstract()
            ->findByFkResourceProductAbstract($this->productAbstractTransfer->getIdProductAbstract());

        $this->assertNotNull($urlCollectionEntity);
        $this->assertNotNull($urlCollectionEntity->count());

        $this->assertNotNull($spyProductConcreteStorage);
        $data = $spyProductConcreteStorage->getData();

        $this->assertSame($this->productConcreteTransfer->getSku(), $data['sku']);
        $this->assertSame(1, count($data['attributes']));
        $this->assertSame($urlCollectionEntity->getLast()->getUrl(), $data['url']);
    }

    /**
     * @return array
     */
    protected function getIdStores(): array
    {
        $storeIds = [];

        foreach ($this->getStoreFacade()->getAllStores() as $storeTransfer) {
            $storeIds[] = $storeTransfer->getIdStore();
        }

        return $storeIds;
    }

    /**
     * @param \Generated\Shared\Transfer\ProductAbstractTransfer $productAbstractTransfer
     *
     * @return void
     */
    protected function addStoreRelationToProductAbstracts(ProductAbstractTransfer $productAbstractTransfer): void
    {
        $idStores = $this->getIdStores();

        $productAbstractTransfer->setStoreRelation((new StoreRelationTransfer())->setIdStores($idStores));

        $this->tester->getProductFacade()->saveProductAbstract($productAbstractTransfer);
    }

    /**
     * @return \Spryker\Zed\Store\Business\StoreFacadeInterface
     */
    protected function getStoreFacade(): StoreFacadeInterface
    {
        return $this->tester->getLocator()->store()->facade();
    }
}

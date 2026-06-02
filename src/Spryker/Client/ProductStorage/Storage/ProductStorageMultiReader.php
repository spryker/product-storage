<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\ProductStorage\Storage;

use Spryker\Client\ProductStorage\Dependency\Client\ProductStorageToStorageClientInterface;

class ProductStorageMultiReader implements ProductStorageMultiReaderInterface
{
    protected ProductStorageToStorageClientInterface $storageClient;

    public function __construct(ProductStorageToStorageClientInterface $storageClient)
    {
        $this->storageClient = $storageClient;
    }

    /**
     * {@inheritDoc}
     */
    public function getRawProductCollection(array $keys): array
    {
        return $this->storageClient->getMulti($keys);
    }
}

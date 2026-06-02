<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\ProductStorage\Storage;

interface ProductStorageMultiReaderInterface
{
    /**
     * Specification:
     * - Fetches multiple storage entries by their keys in a single batch request.
     * - Returns an array keyed by storage key with the raw stored value (string or null) per key.
     *
     * @api
     *
     * @param array<string> $keys
     *
     * @return array<string, string|null>
     */
    public function getRawProductCollection(array $keys): array;
}

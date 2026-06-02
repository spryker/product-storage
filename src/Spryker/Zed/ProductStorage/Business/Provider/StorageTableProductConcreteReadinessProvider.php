<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductStorage\Business\Provider;

use ArrayObject;
use DateTime;
use Generated\Shared\Transfer\ProductConcreteReadinessRequestTransfer;
use Generated\Shared\Transfer\ProductReadinessTransfer;
use Spryker\Client\ProductStorage\ProductStorageClientInterface;
use Spryker\Zed\ProductStorage\Persistence\ProductStorageRepositoryInterface;

class StorageTableProductConcreteReadinessProvider implements ProductConcreteReadinessProviderInterface
{
    protected const string TITLE_IN_STORAGE = 'In Storage table for locale';

    protected const string KEY_LOCALE = 'locale';

    protected const string KEY_UPDATED_AT = 'updated_at';

    protected const string KEY_STORAGE_KEY = 'key';

    protected const string KEY_DATA = 'data';

    protected const string KEY_STORAGE_TIMESTAMP = '_timestamp';

    protected const string FALLBACK_VALUE = '-';

    protected const string FORMAT_DATE_OUTPUT = 'Y-m-d H:i:s';

    protected const string FORMAT_DATE_WITH_UTC = '%s UTC';

    protected const string FORMAT_ROW = '%s, storage: %s &mdash; Last updated. DB: <strong>%s</strong>. Storage: <strong>%s</strong>. Status: %s';

    protected const string FORMAT_STORAGE_KEY_LINK = '<a href="/storage-gui/maintenance/key?key=%s" target="_blank">%s</a>';

    protected const string STATUS_HTML_SYNCED = '<span style="color:green;font-weight:bold">Synced</span>';

    protected const string STATUS_HTML_UNSYNCED = '<span style="color:red;font-weight:bold">Unsynced</span>';

    public function __construct(
        protected ProductStorageRepositoryInterface $productStorageRepository,
        protected ProductStorageClientInterface $productStorageClient,
    ) {
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

        $productReadinessTransfers->append(
            (new ProductReadinessTransfer())
                ->setTitle(static::TITLE_IN_STORAGE)
                ->setValues($this->buildRowValues($productConcreteStorageData)),
        );

        return $productReadinessTransfers;
    }

    /**
     * @param array<array<string, mixed>> $productConcreteStorageData
     *
     * @return array<string>
     */
    protected function buildRowValues(array $productConcreteStorageData): array
    {
        if (!$productConcreteStorageData) {
            return [static::FALLBACK_VALUE];
        }

        $storageKeys = array_filter(array_column($productConcreteStorageData, static::KEY_STORAGE_KEY));
        $storageDataByKey = $storageKeys ? $this->productStorageClient->getRawProductCollection($storageKeys) : [];

        $values = [];

        foreach ($productConcreteStorageData as $row) {
            $values[] = $this->formatRow($row, $storageDataByKey);
        }

        return $values;
    }

    /**
     * @param array<string, mixed> $row
     * @param array<string, mixed> $storageDataByKey
     */
    protected function formatRow(array $row, array $storageDataByKey): string
    {
        $localeName = $row[static::KEY_LOCALE] ?? static::FALLBACK_VALUE;
        $dbUpdatedAt = $row[static::KEY_UPDATED_AT] ?? null;
        $storageKey = $row[static::KEY_STORAGE_KEY] ?? null;

        $dbFormatted = $dbUpdatedAt !== null
            ? sprintf(static::FORMAT_DATE_WITH_UTC, $this->formatUpdatedAt($dbUpdatedAt))
            : static::FALLBACK_VALUE;

        $rawStorageData = $storageKey !== null ? ($storageDataByKey['kv:' . $storageKey] ?? null) : null;
        $storageData = $rawStorageData !== null ? json_decode($rawStorageData, true) : null;
        $storageFormatted = $this->formatStorageTimestamp($storageData);

        $dbDataRaw = $row[static::KEY_DATA] ?? null;
        $dbData = is_string($dbDataRaw) ? json_decode($dbDataRaw, true) : $dbDataRaw;

        $statusHtml = $this->isSynced($dbData, $storageData)
            ? static::STATUS_HTML_SYNCED
            : static::STATUS_HTML_UNSYNCED;

        $storageKeyLink = $storageKey !== null
            ? sprintf(static::FORMAT_STORAGE_KEY_LINK, $storageKey, $storageKey)
            : static::FALLBACK_VALUE;

        return sprintf(static::FORMAT_ROW, $localeName, $storageKeyLink, $dbFormatted, $storageFormatted, $statusHtml);
    }

    /**
     * @param array<string, mixed>|null $storageData
     */
    protected function formatStorageTimestamp(?array $storageData): string
    {
        if ($storageData === null) {
            return static::FALLBACK_VALUE;
        }

        $timestamp = $storageData[static::KEY_STORAGE_TIMESTAMP] ?? null;

        if ($timestamp === null) {
            return static::FALLBACK_VALUE;
        }

        $dateTime = (new DateTime())->setTimestamp((int)$timestamp);

        return sprintf(static::FORMAT_DATE_WITH_UTC, $dateTime->format(static::FORMAT_DATE_OUTPUT));
    }

    /**
     * @param array<string, mixed>|null $dbData
     * @param array<string, mixed>|null $storageData
     */
    protected function isSynced(?array $dbData, ?array $storageData): bool
    {
        if ($dbData === null || $storageData === null) {
            return false;
        }

        $storageDataWithoutTimestamp = $storageData;
        unset($storageDataWithoutTimestamp[static::KEY_STORAGE_TIMESTAMP]);

        return $dbData === $storageDataWithoutTimestamp;
    }

    protected function formatUpdatedAt(?string $updatedAt): string
    {
        if ($updatedAt === null) {
            return static::FALLBACK_VALUE;
        }

        $dateTime = DateTime::createFromFormat('Y-m-d H:i:s.u', $updatedAt)
            ?: DateTime::createFromFormat('Y-m-d H:i:s', $updatedAt);

        if ($dateTime === false) {
            return $updatedAt;
        }

        return $dateTime->format(static::FORMAT_DATE_OUTPUT);
    }
}

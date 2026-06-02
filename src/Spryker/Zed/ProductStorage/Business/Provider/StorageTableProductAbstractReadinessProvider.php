<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductStorage\Business\Provider;

use ArrayObject;
use DateTime;
use Generated\Shared\Transfer\ProductAbstractReadinessRequestTransfer;
use Generated\Shared\Transfer\ProductReadinessTransfer;
use Spryker\Client\ProductStorage\ProductStorageClientInterface;
use Spryker\Zed\ProductStorage\Persistence\ProductStorageRepositoryInterface;

class StorageTableProductAbstractReadinessProvider implements ProductAbstractReadinessProviderInterface
{
    protected const string TITLE_IN_STORAGE = 'In Storage table for store/locale';

    protected const string KEY_LOCALE = 'Locale';

    protected const string KEY_LOCALE_NAME = 'locale_name';

    protected const string KEY_UPDATED_AT = 'updated_at';

    protected const string KEY_STORE = 'store';

    protected const string KEY_LOCALE_CODE = 'locale';

    protected const string KEY_STORAGE_KEY = 'key';

    protected const string KEY_DATA = 'data';

    protected const string KEY_STORAGE_TIMESTAMP = '_timestamp';

    protected const string FALLBACK_VALUE = '-';

    protected const string FORMAT_DATE_OUTPUT = 'Y-m-d H:i:s';

    protected const string FORMAT_DATE_WITH_UTC = '%s UTC';

    protected const string FORMAT_ROW = '%s: %s, storage: %s &mdash; Last updated. DB: <strong>%s</strong>. Storage: <strong>%s</strong>. Status: %s';

    protected const string FORMAT_STORAGE_KEY_LINK = '<a href="/storage-gui/maintenance/key?key=%s" target="_blank">%s</a>';

    protected const string STATUS_HTML_SYNCED = '<span style="color:green;font-weight:bold">Synced</span>';

    protected const string STATUS_HTML_UNSYNCED = '<span style="color:red;font-weight:bold">Unsynced</span>';

    public function __construct(
        protected ProductStorageRepositoryInterface $productStorageRepository,
        protected ProductStorageClientInterface $productStorageClient,
    ) {
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
        $productAbstractLocaleData = $this->productStorageRepository->getProductAbstractsByIds([$idProductAbstract]);
        $storageEntries = $this->productStorageRepository->getProductAbstractStorageEntriesByIdProductAbstract($idProductAbstract);

        $storageKeys = array_filter(array_column($storageEntries, static::KEY_STORAGE_KEY));
        $storageDataByKey = $storageKeys ? $this->buildStorageKeyLookup(
            $this->productStorageClient->getRawProductCollection($storageKeys),
            array_values($storageKeys),
        ) : [];

        $dbUpdatedAtByLocale = $this->buildDbUpdatedAtByLocale($productAbstractLocaleData);

        $productReadinessTransfers->append(
            (new ProductReadinessTransfer())
                ->setTitle(static::TITLE_IN_STORAGE)
                ->setValues($this->buildStorageRowValues($storageEntries, $dbUpdatedAtByLocale, $storageDataByKey)),
        );

        return $productReadinessTransfers;
    }

    /**
     * @param array<int, array<string, mixed>> $storageEntries
     * @param array<string, string|null> $dbUpdatedAtByLocale
     * @param array<string, mixed> $storageDataByKey
     *
     * @return array<string>
     */
    protected function buildStorageRowValues(array $storageEntries, array $dbUpdatedAtByLocale, array $storageDataByKey): array
    {
        if (!$storageEntries && !$dbUpdatedAtByLocale) {
            return [static::FALLBACK_VALUE];
        }

        $values = [];
        $localesInStorage = [];

        foreach ($storageEntries as $entry) {
            $locale = $entry[static::KEY_LOCALE_CODE];
            $localesInStorage[$locale] = true;
            $values[] = $this->formatStorageRow($entry, $dbUpdatedAtByLocale[$locale] ?? null, $storageDataByKey);
        }

        foreach ($dbUpdatedAtByLocale as $locale => $dbUpdatedAt) {
            if (isset($localesInStorage[$locale])) {
                continue;
            }

            $dbFormatted = $dbUpdatedAt !== null
                ? sprintf(static::FORMAT_DATE_WITH_UTC, $this->formatUpdatedAt($dbUpdatedAt))
                : static::FALLBACK_VALUE;

            $values[] = sprintf(static::FORMAT_ROW, $locale, static::FALLBACK_VALUE, static::FALLBACK_VALUE, $dbFormatted, static::FALLBACK_VALUE, static::STATUS_HTML_UNSYNCED);
        }

        return $values;
    }

    /**
     * @param array<string, mixed> $entry
     * @param array<string, mixed> $storageDataByKey
     */
    protected function formatStorageRow(array $entry, ?string $dbUpdatedAt, array $storageDataByKey): string
    {
        $locale = $entry[static::KEY_LOCALE_CODE];
        $store = $entry[static::KEY_STORE];
        $storageKey = $entry[static::KEY_STORAGE_KEY] ?? null;

        $dbFormatted = $dbUpdatedAt !== null
            ? sprintf(static::FORMAT_DATE_WITH_UTC, $this->formatUpdatedAt($dbUpdatedAt))
            : static::FALLBACK_VALUE;

        $rawStorageData = $storageKey !== null ? ($storageDataByKey[$storageKey] ?? null) : null;
        $storageData = $rawStorageData !== null ? json_decode($rawStorageData, true) : null;
        $storageFormatted = $this->formatStorageTimestamp($storageData);

        $statusHtml = $this->isSynced($entry[static::KEY_DATA] ?? null, $storageData)
            ? static::STATUS_HTML_SYNCED
            : static::STATUS_HTML_UNSYNCED;

        $storageKeyLink = $storageKey !== null
            ? sprintf(static::FORMAT_STORAGE_KEY_LINK, $storageKey, $storageKey)
            : static::FALLBACK_VALUE;

        return sprintf(static::FORMAT_ROW, $locale, $store, $storageKeyLink, $dbFormatted, $storageFormatted, $statusHtml);
    }

    /**
     * @param array<string, mixed> $storageDataByPrefixedKey
     * @param array<string> $originalKeys
     *
     * @return array<string, mixed>
     */
    protected function buildStorageKeyLookup(array $storageDataByPrefixedKey, array $originalKeys): array
    {
        $originalKeySet = array_flip($originalKeys);
        $lookup = [];

        foreach ($storageDataByPrefixedKey as $prefixedKey => $value) {
            if (isset($originalKeySet[$prefixedKey])) {
                $lookup[$prefixedKey] = $value;

                continue;
            }

            $colonPosition = strpos($prefixedKey, ':');
            if ($colonPosition === false) {
                continue;
            }

            $keyWithoutPrefix = substr($prefixedKey, $colonPosition + 1);
            if (isset($originalKeySet[$keyWithoutPrefix])) {
                $lookup[$keyWithoutPrefix] = $value;
            }
        }

        return $lookup;
    }

    /**
     * @param array<string, mixed>|null $storageData
     */
    protected function formatStorageTimestamp(?array $storageData): string
    {
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

    /**
     * @param array<mixed> $productAbstractLocaleData
     *
     * @return array<string, string|null>
     */
    protected function buildDbUpdatedAtByLocale(array $productAbstractLocaleData): array
    {
        $dbUpdatedAtByLocale = [];

        foreach ($productAbstractLocaleData as $row) {
            $locale = $row[static::KEY_LOCALE][static::KEY_LOCALE_NAME];
            $dbUpdatedAtByLocale[$locale] = $row[static::KEY_UPDATED_AT] ?? null;
        }

        return $dbUpdatedAtByLocale;
    }
}

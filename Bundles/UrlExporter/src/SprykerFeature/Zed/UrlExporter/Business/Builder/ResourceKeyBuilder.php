<?php

namespace SprykerFeature\Zed\UrlExporter\Business\Builder;

use SprykerFeature\Shared\UrlExporter\Code\KeyBuilder\ResourceKeyBuilder as SharedKeyBuilder;

class ResourceKeyBuilder extends SharedKeyBuilder
{
    /**
     * @param array $data
     * @param string $localeName
     *
     * @return string
     */
    public function generateKey($data, $localeName)
    {
        $identifier = $data['value'];
        $this->setResourceType($data['resourceType']);

        return parent::generateKey($identifier, $localeName);
    }

    /**
     * @var string
     */
    protected $resourceType;

    /**
     * @param string $resourceType
     *
     * @return $this
     */
    public function setResourceType($resourceType)
    {
        $this->resourceType = $resourceType;

        return $this;
    }

    /**
     * @return string
     */
    protected function getResourceType()
    {
        return $this->resourceType;
    }
}

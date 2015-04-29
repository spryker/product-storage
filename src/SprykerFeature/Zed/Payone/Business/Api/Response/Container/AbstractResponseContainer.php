<?php

namespace SprykerFeature\Zed\Payone\Business\Api\Response\Container;
use SprykerFeature\Shared\Payone\PayoneApiConstants;


abstract class AbstractResponseContainer implements PayoneApiConstants
{

    /**
     * @var string
     */
    protected $status;
    /**
     * @var string
     */
    protected $rawResponse;
    /**
     * @var string
     */
    protected $errorcode;
    /**
     * @var string
     */
    protected $errormessage;
    /**
     * @var string
     */
    protected $customermessage;


    /**
     * @param array $params
     */
    function __construct(array $params = array())
    {
        if (count($params) > 0) {
            $this->init($params);
        }
    }

    /**
     * @param array $data
     */
    public function init(array $data = array())
    {
        foreach ($data as $key => $value) {
            $key = ucwords(str_replace('_', ' ', $key));
            $method = 'set' . str_replace(' ', '', $key);

            if (method_exists($this, $method)) {
                $this->{$method}($value);
            }
        }
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $result = array();
        foreach ($this as $key => $data) {
            if ($data === null) {
                continue;
            }
            else {
                $result[$key] = $data;
            }
        }
        return $result;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $stringArray = array();
        foreach ($this->toArray() as $key => $value) {
            $stringArray[] = $key . '=' . $value;
        }
        $result = implode('|', $stringArray);
        return $result;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $customermessage
     */
    public function setCustomermessage($customermessage)
    {
        $this->customermessage = $customermessage;
    }

    /**
     * @return string
     */
    public function getCustomermessage()
    {
        return $this->customermessage;
    }

    /**
     * @param string $errorcode
     */
    public function setErrorcode($errorcode)
    {
        $this->errorcode = $errorcode;
    }

    /**
     * @return string
     */
    public function getErrorcode()
    {
        return $this->errorcode;
    }

    /**
     * @param string $errormessage
     */
    public function setErrormessage($errormessage)
    {
        $this->errormessage = $errormessage;
    }

    /**
     * @return string
     */
    public function getErrormessage()
    {
        return $this->errormessage;
    }

    /**
     * @param string $key
     * @return null|mixed
     */
    public function getValue($key)
    {
        return $this->get($key);
    }

    /**
     * @param string $key
     * @param string $name
     * @return boolean|null
     */
    public function setValue($key, $name)
    {
        return $this->set($key, $name);
    }

    /**
     * @param $name
     * @return null|mixed
     */
    protected function get($name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }
        return null;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return boolean|null
     */
    protected function set($name, $value)
    {
        if (property_exists($this, $name)) {
            $this->$name = $value;
            return true;
        }
        return null;
    }

    /**
     * @param $rawResponse
     */
    public function setRawResponse($rawResponse)
    {
        $this->rawResponse = $rawResponse;
    }

    /**
     * @return null
     */
    public function getRawResponse()
    {
        return $this->rawResponse;
    }

}

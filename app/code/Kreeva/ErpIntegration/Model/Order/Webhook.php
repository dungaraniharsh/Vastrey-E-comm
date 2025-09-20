<?php

namespace Kreeva\ErpIntegration\Model\Order;

use Kreeva\ErpIntegration\Api\Order\WebhookInterface;
use Magento\Framework\DataObject;

class Webhook extends DataObject implements WebhookInterface
{
    /**
     * Retrieve type.
     *
     * @return mixed|null
     */
    public function getType()
    {
        return $this->_getData(self::KEY_TYPE);
    }

    /**
     * Set type.
     *
     * @param mixed|null $type
     * @return $this
     */
    public function setType($type)
    {
        return $this->setData(self::KEY_TYPE, $type);
    }

    /**
     * Retrieve payload.
     *
     * @return mixed|null
     */
    public function getPayload()
    {
        return $this->_getData(self::KEY_PAYLOAD);
    }

    /**
     * Set payload.
     *
     * @param mixed|null $payload
     * @return $this
     */
    public function setPayload($payload)
    {
        return $this->setData(self::KEY_PAYLOAD, $payload);
    }
}

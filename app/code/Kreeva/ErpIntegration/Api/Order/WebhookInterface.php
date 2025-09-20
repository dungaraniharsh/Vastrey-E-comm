<?php

namespace Kreeva\ErpIntegration\Api\Order;

interface WebhookInterface
{
    const KEY_TYPE = 'type';
    const KEY_PAYLOAD = 'payload';

    /**
     * Retrieve type.
     *
     * @return mixed|null
     */
    public function getType();

    /**
     * Set type.
     *
     * @param mixed|null $type
     * @return $this
     */
    public function setType($type);

    /**
     * Retrieve payload.
     *
     * @return mixed|null
     */
    public function getPayload();

    /**
     * Set payload.
     *
     * @param mixed|null $payload
     * @return $this
     */
    public function setPayload($payload);
}

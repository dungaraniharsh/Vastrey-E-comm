<?php

namespace Kreeva\ErpIntegration\Api\Order;


interface WebhookManagementInterface {


	/**
	 * order Webhook api
	 * @param \Kreeva\ErpIntegration\Api\Order\WebhookInterface[] $payload
	 * @return string
	 */

	public function webhook($payload);
}

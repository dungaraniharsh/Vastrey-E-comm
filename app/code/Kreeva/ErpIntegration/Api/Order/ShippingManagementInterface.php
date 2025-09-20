<?php

namespace Kreeva\ErpIntegration\Api\Order;


interface ShippingManagementInterface {


	/**
	 * order shipping api
	 * @param mixed $data
	 * @return string
	 */

	public function ship($data);
}

<?php

namespace Kreeva\ErpIntegration\Api\Order;


interface InvoiceManagementInterface {


	/**
	 * order invoice api
	 * @param mixed $data
	 * @return string
	 */

	public function invoice($data);
}

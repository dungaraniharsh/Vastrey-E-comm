<?php

namespace Kreeva\ErpIntegration\Model\Source;

class OrderUrls
{
    const CREATE_URL = 'order-list/online/service/create';

    const STATUS_CHANGE_URL = 'order-list/online/service/status-update/{{orderId}}';
}

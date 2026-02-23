<?php

namespace App\Enums;

enum EzApiEndpointEnum : string
{

    case GET_PRODUCT = '/v2/products?sku={SKU_CODE}';

    //case CREATE_ORDER = '/v2/orders/instantTEMPTEST'; //TODO: to simulate & test error handler when invalid api endpoint
    case CREATE_ORDER = '/v2/orders/instant';


    case POLLING_STATUS_ORDER = '/v2/orders?transactionId={TRANSACTION_ID}';
    case GET_ORDER_REDEEM_CODE = '/v2/orders/{TRANSACTION_ID}/codes';


    public function getMethod(): string {
        return match($this) {
            self::GET_PRODUCT, self::POLLING_STATUS_ORDER, self::GET_ORDER_REDEEM_CODE => HttpMethodEnum::GET->value,
            self::CREATE_ORDER => HttpMethodEnum::POST->value,
        };
    }
}

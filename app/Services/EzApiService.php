<?php

namespace App\Services;

use App\Enums\HttpMethodEnum;
use App\Services\Contracts\EzApiServiceInterface;
use Illuminate\Support\Facades\Http;
use App\Enums\EzApiEndpointEnum;

class EzApiService implements EzApiServiceInterface
{
    protected  $httpClient;


    public function __construct()
    {
        //to init http client with auth api credentials
        $this->httpClient = Http::withHeaders(['x-api-key' => config('ezApi.api_key'),
                                                'Authorization' => 'Bearer ' . config('ezApi.access_token')])
                                    ->withOptions(['verify' => false]); //to temp resolve issue about 'cURL error 60: SSL certificate problem: unable to get local issuer certificate' at local dev when PHP/cURL cannot verify the SSL certificate of the server it's trying to communicate with
                                //->timeout(config('ezApi.order_fulfillment_timeout'));


    }


    //TODO: ok
    private function _sendRequest(?string $endpoint='',
                                  string $method = HttpMethodEnum::GET->value,
                                  array|\JsonSerializable|\Illuminate\Contracts\Support\Arrayable $data=[]): ?array
    {

        //set base url of EZ Api + Api endpoint
        $url = config('ezApi.base_url') . $endpoint;

        //make request with data (if any)
        $response = $this->httpClient->{$method}($url, $data);


        //convert string json to array
        $responseBodyArray = json_decode($response->getBody(), true);

        //to debug
        //dump($response, $responseBodyArray);

        return $responseBodyArray;
    }

    public function getSkuCode()
    {
        return config('ezApi.ez_sku');
    }


    /** GET /v2/products?sku={SKU_CODE} */
    public function getProduct(?string $skuCode = null): ?array
    {
        $response = null;

        //set endpoint param
        $skuCode = $skuCode?? $this->getSkuCode();
        $endpointEnum = EzApiEndpointEnum::GET_PRODUCT;
        $endpointMethod = $endpointEnum->getMethod();
        $endpoint = strtr($endpointEnum->value, ['{SKU_CODE}' => $skuCode]);

        //set request data
        $requestData = [];

        //send request
        $response = $this->_sendRequest($endpoint, $endpointMethod, $requestData);

        //TODO: to debug
        //dump($response);

        return $response;
    }


    //TODO: tbc?
    /** POST /v2/orders/instant */
    public function createOrder(?string $clientOrderNumber = null, ?string $skuCode = null): ?array
    {
        $response = null;

        //set endpoint param
        $skuCode = $skuCode?? $this->getSkuCode();
        $endpointEnum = EzApiEndpointEnum::CREATE_ORDER;
        $endpointMethod = $endpointEnum->getMethod();
        $endpoint = $endpointEnum->value;

        //set request data
        $requestData = ['clientOrderNumber' => $clientOrderNumber,
                        'sku' => $skuCode];

        //send request
        $response = $this->_sendRequest($endpoint, $endpointMethod, $requestData);


        //TODO: [tbc?] to handle response

        //TODO: to debug
        //dump($response);

        return $response;

    }

    //TODO: tbc?
    /** POST /v2/orders?transactionId={TRANSACTION_ID} */
    public function pollingOrderStatus(string $ezTransactionId): ?array
    {
        $response = null;

        //set endpoint param
        $endpointEnum = EzApiEndpointEnum::POLLING_STATUS_ORDER;
        $endpointMethod = $endpointEnum->getMethod();
        $endpoint = strtr($endpointEnum->value, ['{TRANSACTION_ID}' => $ezTransactionId]);

        //set request data
        $requestData = ['transactionId' => $ezTransactionId];

        //send request
        $response = $this->_sendRequest($endpoint, $endpointMethod, $requestData);


        //TODO: [tbc?] to handle response

        //TODO: to debug
        //dump($ezTransactionId, $endpointMethod, $endpoint, $response); exit();

        return $response;
    }

    //TODO: tbc?
    /** POST /v2/orders/{TRANSACTION_ID}/codes */
    public function getOrderRedeemCode(string $ezTransactionId): ?array
    {
        $response = null;

        //set endpoint param
        $endpointEnum = EzApiEndpointEnum::GET_ORDER_REDEEM_CODE;
        $endpointMethod = $endpointEnum->getMethod();
        $endpoint = strtr($endpointEnum->value, ['{TRANSACTION_ID}' => $ezTransactionId]);

        //set request data
        $requestData = [];

        //send request
        $response = $this->_sendRequest($endpoint, $endpointMethod, $requestData);


        //TODO: [tbc?] to handle response

        //TODO: to debug
        //dump($ezTransactionId, $endpointMethod, $endpoint, $response); exit();

        return $response;

    }


}

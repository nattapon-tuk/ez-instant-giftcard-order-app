<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;

use Illuminate\Support\Facades\Concurrency;

class OrderServiceTest extends TestCase
{
    use RefreshDatabase;


    public function test_case1_status_mapping_processing(): void
    {

        //create fake records
        $ezOrder = \App\Models\EzOrder::factory()->create([
            'ezTransactionId' => '4610',
            'ezOrderStatus' => 'PROCESSING',
            'redeemCode' => null,
            'isReserved' => '1',
        ]);

        $localOrder = \App\Models\LocalOrder::factory()->create([
            'localOrderId' => 'TXNTEST-100000001',
            'localStatus' => 'PROCESSING',
            'ezTransactionId' => '4610',
        ]);

        // Load the JSON fixture content
        $jsonResponse = file_get_contents(base_path('tests/Fixtures/api_response_ezOrder_status_PROCESSING.json'));
        $jsonResponseFromPolling = file_get_contents(base_path('tests/Fixtures/api_response_ezOrder_status_PROCESSING-POLLING.json'));

        // fake HTTP responses for both create-order & polling-status endpoints of EZ Api
        Http::fake([
            'https://sandboxapi.ezcards.io/v2/orders/instant*' => Http::response($jsonResponse, 200),
            'https://sandboxapi.ezcards.io/v2/orders?transactionId=*' => Http::response($jsonResponseFromPolling, 200),
        ]);

        // Set a config value for this specific test
        Config::set('ezApi.order_fulfillment_timeout', 10); //lower timemout limit for polling status to get 'PROCESSING' returned more quickly


        $closureCreateOrder = function () {
            return $this->postJson('/orders');
        };
        $closurePollingStatus = function () {
            $localOrderId = 'TXNTEST-100000001';
            //sleep(1); // to simulate delaying 2nd request for polling latest status
            return $this->getJson('/orders/'.$localOrderId);
        };

        // Run multiple tasks concurrently
//        [$response, $responsePollingStatus] = Concurrency::run([
//            $closureCreateOrder,
//            $closurePollingStatus
//        ]);

        $responsePollingStatus = $closurePollingStatus();

        $responsePollingStatus->assertStatus(200)
            ->assertJson([
                'status' => 'PROCESSING',
            ]);

    }

    public function test_case2_status_mapping_completed(): void
    {
        // Load the JSON fixture content
        $jsonResponse = file_get_contents(base_path('tests/Fixtures/api_response_ezOrder_status_PROCESSING.json'));
        $jsonResponseFromPolling = file_get_contents(base_path('tests/Fixtures/api_response_ezOrder_status_COMPLETED-POLLING.json'));

        // fake HTTP responses for both create-order & polling-status endpoints of EZ Api
        Http::fake([
            'https://sandboxapi.ezcards.io/v2/orders/instant*' => Http::response($jsonResponse, 200),
            'https://sandboxapi.ezcards.io/v2/orders?transactionId=*' => Http::response($jsonResponseFromPolling, 200),
        ]);

        // Set a config value for this specific test
        //Config::set('ezApi.order_fulfillment_timeout', 0);

        $closureCreateOrder = function () {
            return $this->postJson('/orders');
        };
        $closurePollingStatus = function () {
            $localOrderId = 'TXNTEST-100000001';
            //sleep(1); // to simulate delaying 2nd request for polling latest status
            return $this->getJson('/orders/'.$localOrderId);
        };

        $responseCreateOrder = $closureCreateOrder();
        $responsePollingStatus = $closurePollingStatus();

        $responsePollingStatus->assertStatus(200)
            ->assertJson([
                'status' => 'COMPLETED',
            ]);

    }

    public function test_case3_status_mapping_cancelled(): void
    {

        // Load the JSON fixture content
        $jsonResponse = file_get_contents(base_path('tests/Fixtures/api_response_ezOrder_status_PROCESSING.json'));
        $jsonResponseFromPolling = file_get_contents(base_path('tests/Fixtures/api_response_ezOrder_status_PROCESSING-POLLING.json'));

        // fake HTTP responses for both create-order & polling-status endpoints of EZ Api
        Http::fake([
            'https://sandboxapi.ezcards.io/v2/orders/instant*' => Http::response($jsonResponse, 200),
            'https://sandboxapi.ezcards.io/v2/orders?transactionId=*' => Http::response($jsonResponseFromPolling, 200),
        ]);

        // Set a config value for this specific test
        Config::set('ezApi.order_fulfillment_timeout', 10); //lower timemout limit for polling status to get 'CANCELLED' returned more quickly

        $closureCreateOrder = function () {
            return $this->postJson('/orders');
        };
        $closurePollingStatus = function () {
            $localOrderId = 'TXNTEST-100000001';
            //sleep(1); // to simulate delaying 2nd request for polling latest status
            return $this->getJson('/orders/'.$localOrderId);
        };

        $responseCreateOrder = $closureCreateOrder();
        $responsePollingStatus = $closurePollingStatus();

        $responsePollingStatus->assertStatus(200)
            ->assertJson([
                'status' => 'CANCELLED',
            ]);
    }


    public function test_case4_timeout_cancelled_behavior(): void
    {
        // Load the JSON fixture content
        $jsonResponse = file_get_contents(base_path('tests/Fixtures/api_response_ezOrder_status_PROCESSING.json'));

        // fake HTTP responses for both create-order & polling-status endpoints of EZ Api
        Http::fake([
            'https://sandboxapi.ezcards.io/v2/orders/instant*' => Http::response($jsonResponse, 200),

        ]);

        // Set a config value for this specific test
        Config::set('ezApi.order_fulfillment_timeout', 0); //to simulate CANCELLED return when reach timeout limit

        // Now, when your application makes an HTTP call to the external API, it will return the fixture data
        $response = $this->postJson('/orders'); // An internal endpoint that calls the external API

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'CANCELLED',
            ]);
    }

    public function test_case5_reuse_logic(): void
    {
        //create fake records
        $ezOrder = \App\Models\EzOrder::factory()->create([
                'ezTransactionId' => '4610',
                'ezOrderStatus' => 'COMPLETED',
                'redeemCode' => '02a119e6-ee83-4737-b026-5f091413781e',
                'isReserved' => '0',
                ]);

        $localOrder = \App\Models\LocalOrder::factory()->create([
                'localOrderId' => 'TXNTEST-100000001',
                'localStatus' => 'CANCELLED',
                'ezTransactionId' => '4610',
            ]);

        // Load the JSON fixture content
        $jsonResponse = file_get_contents(base_path('tests/Fixtures/api_response_ezOrder_status_PROCESSING.json'));

        // fake HTTP responses for both create-order & polling-status endpoints of EZ Api
        Http::fake([
            'https://sandboxapi.ezcards.io/v2/orders/instant*' => Http::response($jsonResponse, 200),

        ]);



        // Now, when your application makes an HTTP call to the external API, it will return the fixture data
        $response = $this->postJson('/orders'); // An internal endpoint that calls the external API


        // Assert: Check if a record with the specified attributes exists in the 'localOrder' table
        $this->assertDatabaseHas('local_orders', [
            'localOrderId' => 'TXNTEST-100000002',
            'ezTransactionId' => '4610'
        ]);


    }


    public function test_case6_one_to_one_rule(): void
    {
        //create
        $ezOrder = \App\Models\EzOrder::factory()->create([
            'ezTransactionId' => '4610',
            'ezOrderStatus' => 'COMPLETED',
            'redeemCode' => '02a119e6-ee83-4737-b026-5f091413781e',
            'isReserved' => '0',
        ]);

        $localOrder = \App\Models\LocalOrder::factory()->create([
            'localOrderId' => 'TXNTEST-100000001',
            'localStatus' => 'CANCELLED',
            'ezTransactionId' => '4610',
        ]);

        // Load the JSON fixture content
        $jsonResponse = file_get_contents(base_path('tests/Fixtures/api_response_ezOrder_status_PROCESSING.json'));

        // fake HTTP responses for both create-order & polling-status endpoints of EZ Api
        Http::fake([
            'https://sandboxapi.ezcards.io/v2/orders/instant*' => Http::response($jsonResponse, 200),
        ]);



        //to send 1st Create-Order request, should map with available EZ order in DB
        $response = $this->postJson('/orders');

        $closureCreateOrder = function () {
            return $this->postJson('/orders');
        };


        // Run multiple tasks concurrently
        [$response_1, $response_2] = Concurrency::run([
            $closureCreateOrder, //to send 1st Create-Order request, should map with available EZ order in DB
            $closureCreateOrder, //to send 2nd Create-Order request, should no longer have available EZ order & send api request to get new EZ order
        ]);



        // Assert: Check if a record with the specified attributes exists in the 'localOrder' table
        $this->assertDatabaseHas('local_orders', [
            'localOrderId' => 'TXNTEST-100000002',
            'ezTransactionId' => '4610',
            'localStatus' => 'COMPLETED',
        ]);

        // Assert: Check if a record with the specified attributes exists in the 'localOrder' table
        $this->assertDatabaseHas('local_orders', [
            'localOrderId' => 'TXNTEST-100000003',
            'ezTransactionId' => '4612', //get new ez transaction id from sending api request as no available EZ order in DB
            'localStatus' => 'COMPLETED',
        ]);

    }


}

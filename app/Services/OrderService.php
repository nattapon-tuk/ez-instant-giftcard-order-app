<?php

namespace App\Services;

use App\Enums\EzOrderStatusEnum;
use App\Enums\OrderStatusEnum;
use App\Models\EzOrder;
use App\Models\LocalOrder;
use App\Repositories\LocalOrderRepository;
use App\Repositories\EzOrderRepository;
use App\Services\Contracts\EzApiServiceInterface;
use App\Services\Contracts\OrderServiceInterface;
use Illuminate\Support\Arr;

class OrderService implements OrderServiceInterface
{

    protected LocalOrderRepository $localOrderRepo;

    protected EzOrderRepository $ezOrderRepo;

    protected EzApiServiceInterface $ezApiService;

    //to configure
    protected $confOrderFulfillmentTimeout;
    protected $confPollIntervalSeconds;

    /**
     * Create a new class instance.
     */
    public function __construct(LocalOrderRepository $localOrderRepo,
                                    EzOrderRepository $ezOrderRepo,
                                    EzApiServiceInterface $ezApiService)
    {
        $this->localOrderRepo = $localOrderRepo;
        $this->ezOrderRepo = $ezOrderRepo;
        $this->ezApiService = $ezApiService;

        $this->confOrderFulfillmentTimeout = config('ezApi.order_fulfillment_timeout', 60);
        $this->confPollIntervalSeconds = config('ezApi.poll_interval_seconds', 2);
    }


    private function _initNewOrder()
    {
        $localOrder = $this->localOrderRepo->createNewOrder();

        return $localOrder;
    }


    //TODO: ongoing
    public function createOrder(): ?LocalOrder
    {

        //TODO
        // [ok] Step1, to find available EZ order for being re-used (if not used yet) + add reservation logic for avoiding two Create-Order calls at the same time
        $availableEzOrder = $this->_findAvailableEzOrder();
        if($availableEzOrder)
        {
            $this->ezOrderRepo->reserveEzOrder($availableEzOrder->ezTransactionId);
        }



        //TODO
        // [ok] Step2, if no available Ez order to re-use, then send api request to create new EZ order
        if(!$availableEzOrder)
        {
            $result = $this->ezApiService->createOrder();

            if(!Arr::has($result, 'data.transactionId'))
            {
                return null; // return null in case of api response not returning correct ez transaction id
            }

            //TODO:
            // [ok] to create new EZ order + reserve order
            $ezOrderData = [
                'ezTransactionId' => Arr::get($result, 'data.transactionId'),
                'ezOrderStatus' => Arr::get($result, 'data.status'),
            ];
            $ezOrder = $this->ezOrderRepo->create($ezOrderData);

            $this->ezOrderRepo->reserveEzOrder($ezOrder->ezTransactionId);
        }
        else
        {
            $ezOrder = $availableEzOrder;
        }



        // to create new local order + point to ezTransactionId
        //$localOrder = $this->_initNewOrder();
        $localOrder = $this->localOrderRepo->createAndPointToEzOrder($ezOrder);


        //TODO:
        // [ok] to polling order status within timeout limit
        if($localOrder->localStatus === OrderStatusEnum::PROCESSING->value)
        {
            $this->_pollingEzOrderStatus($localOrder, $ezOrder);
        }


        return $localOrder;

    }


    //TODO: doing
    private function _findAvailableEzOrder()
    {
        $availableEzOrder = $this->ezOrderRepo->findAvailableEzOrder();

        if($availableEzOrder)
        {
            return $availableEzOrder;
        }
        else
        {
            return false;
        }
    }

    //TODO: ok
    private function _pollingEzOrderStatus(LocalOrder $localOrder, EzOrder $ezOrder, bool $isSkippedToCancel = false): void
    {
        $pollingContinue = true;
        $pollingEndTime = time() + $this->confOrderFulfillmentTimeout;
        while($pollingContinue
                && $localOrder->localStatus === OrderStatusEnum::PROCESSING->value
                && time() < $pollingEndTime
        )
        {
            $pollingResult = $this->ezApiService->pollingOrderStatus($localOrder->ezTransactionId);

            //to debug
            //dump(now(), Arr::get($pollingResult, 'data.items.0.status'), Arr::get($pollingResult, 'data.items.0.status') === EzOrderStatusEnum::COMPLETED->value);

            if(Arr::has($pollingResult, 'data.items.0.status'))
            {
                if(Arr::get($pollingResult, 'data.items.0.status') === EzOrderStatusEnum::COMPLETED->value)
                {
                    //if ezOrderStatus = 'COMPLETED', then fetch redeem code + update both order statuses + exit polling process
                    $getCodeResult = $this->ezApiService->getOrderRedeemCode($localOrder->ezTransactionId);

                    $redeemCode = '';
                    if(Arr::has($getCodeResult, 'data.0.codes.0.redeemCode'))
                    {
                        $redeemCode = Arr::get($getCodeResult, 'data.0.codes.0.redeemCode');
                    }
                    else
                    {
                        break; //exit while if incorrect getCodeResult
                    }

                    $ezOrder->ezOrderStatus = EzOrderStatusEnum::COMPLETED->value;
                    $ezOrder->redeemCode = $redeemCode;
                    $ezOrder->save();

                    $localOrder->localStatus = OrderStatusEnum::COMPLETED->value;
                    $localOrder->save();

                    return;
                }
            }
            else
            {
                $pollingContinue = false;
            }

            // wait interval for next polling process
            sleep($this->confPollIntervalSeconds);
        }

        //if exceed polling timeout limit OR api response is not correct, then update localStatus to 'CANCELLED'
        if($isSkippedToCancel !== true)
        {
            $localOrder->localStatus = OrderStatusEnum::CANCELLED->value;
            $localOrder->save();

            //un-reserve EZ order to be re-usable
            $this->ezOrderRepo->unreserveEzOrder($ezOrder->ezTransactionId);
        }
        else
        {
            //not status update required when invoking polling process from getRedeemCode function based on the requirement
        }

    }


    //TODO: ok
    public function getOrderRedeemCode($localOrderId): ?array
    {
        $localOrder = $this->localOrderRepo->findByOrderId($localOrderId);


        if(!$localOrder)
        {
            return null;
        }


        switch($localOrder->localStatus):
            case OrderStatusEnum::PROCESSING->value:
                $this->_pollingEzOrderStatus($localOrder, $localOrder->ezOrder, true);
                break;
            case OrderStatusEnum::CANCELLED->value:
            case OrderStatusEnum::COMPLETED->value:
            default:

        endswitch;

        //to debug
        //dump($localOrder, $localOrder->localStatus, $localOrder->ezOrder->redeemCode);


        $returnData = [
                        'id' => $localOrder->localOrderId,
                        'status' => $localOrder->localStatus,
                        'redeemCode' => ($localOrder->localStatus === OrderStatusEnum::COMPLETED->value) ? $localOrder->ezOrder->redeemCode : null,
                        'createdAt' => $localOrder->created_at];

        return $returnData;
    }





    //TODO: to test
    public function getAll()
    {

        return $this->localOrderRepo->getAll();
    }

    public function getEzProduct()
    {
        $this->ezApiService->getProduct();
    }


}

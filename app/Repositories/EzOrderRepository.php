<?php

namespace App\Repositories;
use App\Enums\EzOrderStatusEnum;
use App\Enums\OrderStatusEnum;
use App\Models\EzOrder;
use Illuminate\Support\Arr;

class EzOrderRepository
{
    protected $model;

    public function __construct(EzOrder $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model->all();
    }

    public function create(array $data = []) : EzOrder
    {
        return $this->upsert($data);
    }

    public function upsert(array $data = []) : EzOrder
    {
       $result = $this->model->upsert($data, uniqueBy: ['ezTransactionId']);

       $ezOrder = $this->model->where('ezTransactionId', Arr::get($data,'ezTransactionId'))->first();

       //TODO: to debug
       //dump($data,$result, $ezOrder);

       return $ezOrder;
    }

    public function reserveEzOrder(string $ezTransactionId)
    {
        $ezOrder = $this->model->where('ezTransactionId', $ezTransactionId)->first();
        $ezOrder->isReserved = true;
        $ezOrder->save();
    }

    public function unreserveEzOrder(string $ezTransactionId)
    {
        $ezOrder = $this->model->where('ezTransactionId', $ezTransactionId)->first();
        $ezOrder->isReserved = false;
        $ezOrder->save();
    }

    //TODO:
    // - [doing] to find available EZ orders for being re-used (if not used yet)
    public function findAvailableEzOrder()
    {
        $availableEzOrder = $this->model->with('localOrders')
            ->whereIn('ezOrderStatus', [EzOrderStatusEnum::PROCESSING->value, EzOrderStatusEnum::COMPLETED->value])
            ->first();

        $availableEzOrder = $this->model->whereHas('localOrders', function($query){
                $query->where('localStatus', OrderStatusEnum::CANCELLED->value);
            })
            ->where('isReserved', false)
            ->whereIn('ezOrderStatus', [EzOrderStatusEnum::PROCESSING->value, EzOrderStatusEnum::COMPLETED->value])
            ->first();

        //TODO: to debug
        //dump($availableEzOrder);exit();

        return $availableEzOrder;
    }

}

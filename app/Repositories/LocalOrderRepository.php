<?php

namespace App\Repositories;
use App\Enums\EzOrderStatusEnum;
use App\Enums\OrderStatusEnum;
use App\Models\EzOrder;
use App\Models\LocalOrder;
use Illuminate\Support\Arr;

class LocalOrderRepository
{
    protected $model;

    public function __construct(LocalOrder $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model->all();
    }



    public function create(array $data = []) : LocalOrder
    {
        return $this->model->create($data);
    }

    public function createAndPointToEzOrder(EzOrder $ezOrder) : LocalOrder
    {
        //TODO:
        $data = [
            'ezTransactionId' => $ezOrder->ezTransactionId,
            'localStatus' => $ezOrder->ezOrderStatus === EzOrderStatusEnum::COMPLETED->value ? OrderStatusEnum::COMPLETED->value : OrderStatusEnum::PROCESSING->value,
        ];
        return $this->create($data);
    }



    public function createNewOrder()
    {
        $localOrder = $this->model;
        $localOrder->save();

        return $localOrder;
    }

    public function findByOrderId(string $localOrderId)
    {
        return $this->model->with('ezOrder')->where('localOrderId', $localOrderId)->first();
    }

}

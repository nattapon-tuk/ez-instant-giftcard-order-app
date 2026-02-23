<?php

namespace App\Http\Controllers;


use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\Contracts\OrderServiceInterface;
use App\Models\LocalOrder;
use Illuminate\Http\Response;


class OrderController extends Controller
{
    /**
     * The order service implementation.
     */
    protected OrderServiceInterface $orderService;

    public function __construct(OrderServiceInterface $orderService)
    {
        $this->orderService = $orderService;

    }

    //TODO: ok
    public function create(Request $request): JsonResponse
    {

        try
        {
            $result = $this->orderService->createOrder();
        }
        catch (\Exception $e)
        {
            $result = null;
        }

        if(!$result)
        {
            $responseData = ['message' => 'Internal server error'];
            $httpStatus = Response::HTTP_INTERNAL_SERVER_ERROR;
        }
        else
        {
            $responseData = [
                'id' => $result->localOrderId,
                'status' => $result->localStatus,
                'createdAt' => $result->created_at,
            ];
            $httpStatus = Response::HTTP_OK;
        }


        return response()->json($responseData, $httpStatus);
    }

    //TODO: ok
    public function getRedeemCode(Request $request, string $localOrderId): JsonResponse
    {
        try
        {
            $result = $this->orderService->getOrderRedeemCode($localOrderId);
        }
        catch (\Exception $e)
        {
            $result = null;
        }


        if(!$result)
        {
            $responseData = ['message' => 'Record not found.'];
            $httpStatus = Response::HTTP_NOT_FOUND;
        }
        else
        {
            $responseData = $result;
            $httpStatus = Response::HTTP_OK;
        }


        return response()->json($responseData, $httpStatus);
    }

}

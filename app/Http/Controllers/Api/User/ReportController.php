<?php

namespace App\Http\Controllers\Api\User;

use App\Exceptions\InvalidRequestException;
use App\Http\Controllers\Controller;
use App\Http\Services\AffiliationService;
use App\Http\Services\BuyOrderService;
use App\Http\Services\SellOrderService;
use App\Http\Services\StopLimitService;
use App\Http\Services\TradeReferralService;
use App\Http\Services\TransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function getAllOrdersHistoryBuyApp(Request $request): JsonResponse
    {
        return $this->handlerApiResponse(function () use ($request): array {
            $limit = $request->per_page ?: 10;
            $order_data = [
                'column_name' => $request->column_name ?: 'created_at',
                'order_by' => $request->order_by ?: 'DESC',
            ];
            $data = [
                'title' => __('Buy Order History'),
                'type' => 'buy',
                'sub_menu' => 'buy_order',
                'items' => (new BuyOrderService())->getAllOrderHistory(auth()->id(), $request->search, $order_data)->paginate($limit)
            ];
            return $this->responseData(true, $data['title'], $data);
        });
    }

    public function getAllOrdersHistorySellApp(Request $request)
    {
        return $this->handlerApiResponse(function () use ($request) {
            $limit = $request->per_page ?: 10;
            $order_data = [
                'column_name' => $request->column_name ?: 'created_at',
                'order_by' => $request->order_by ?: 'DESC',
            ];
            $data = [
                'title' => __('Sell Order History'),
                'type' => 'sell',
                'sub_menu' => 'sell_order',
                'items' => (new SellOrderService())->getAllOrderHistory(auth()->id(), $request->search, $order_data)->paginate($limit)
            ];
            return responseData(true, $data['title'], $data);
        });
    }

    public function getAllTransactionHistoryApp(Request $request): JsonResponse
    {
        return $this->handlerApiResponse(function () use ($request) {
            $limit = $request->get('per_page', 10);
            $order_data = [
                'column_name' => $request->column_name ?: 'transactions.created_at',
                'order_by' => $request->order_by ?: 'DESC',
                'search' => $request->search
            ];
            $data = [
                'title' => __('Transaction History'),
                'sub_menu' => 'transaction',
                'items' => (new TransactionService())->getMyAllTransactionHistory(auth()->id(), $order_data)->paginate($limit)
            ];
            return responseData(true, $data['title'], $data);
        });
    }

    public function getExchangeAllStopLimitOrdersApp(Request $request): JsonResponse
    {
        return $this->handlerApiResponse(function () use ($request) {
            $data['items'] = (new StopLimitService())->getMyStopLimitOrders($request);
            return responseData(true, __('All stop limit order'), $data);
        });
    }

    public function getReferralHistory(Request $request): JsonResponse
    {
        return $this->handlerApiResponse(function () use ($request): array {
            $limit = $request->limit ?: 25;
            $offset = $request->page ?: 1;

            if (!$request->has('type'))
                throw new InvalidRequestException(__('Type is required!'));

            if (!in_array($request->type, [REFERRAL_TYPE_WITHDRAWAL, REFERRAL_TYPE_TRADE]))
                throw new InvalidRequestException(__('Invalid type!'));

            $userId = auth()->id();
            if (empty($userId))
                throw new InvalidRequestException(__('User not found!'));

            switch ((int) $request->type) {
                case REFERRAL_TYPE_WITHDRAWAL:
                    $affiliationService = new AffiliationService();
                    $response = $affiliationService->getWithdrawalReferralHistoryWithPaginate($userId, $limit, $offset, $request->search);
                    break;
                case REFERRAL_TYPE_TRADE:
                    $tradeReferralService = new TradeReferralService();
                    $response = $tradeReferralService->getAllReferralHistoryWithPaginate($userId, $limit, $offset, $request->search);
                    break;
            }
            return $response;
        });
    }
}

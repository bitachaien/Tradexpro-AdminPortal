<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\NetworkAddressRequest;
use App\Http\Requests\Api\User\WalletRateRequest;
use App\Http\Requests\Api\User\WithdrawalRequest;
use App\Http\Requests\CoinSwapRequest;
use App\Http\Services\Logger;
use App\Http\Services\TransService;
use App\Http\Services\WalletService;
use App\Http\Services\ProgressStatusService;
use App\Model\Coin;
use App\Model\Wallet;
use App\Model\WalletSwapHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Nette\Utils\Json;

class WalletController extends Controller
{
    public WalletService $service;
    public TransService $transService;
    public ProgressStatusService $progressService;

    public function __construct()
    {
        $this->service = new WalletService();
        $this->transService = new TransService();
        $this->progressService = new ProgressStatusService();
    }

    public function walletList(Request $request): JsonResponse
    {
        return $this->handlerApiResponse(function () use ($request): array {
            return $this->service->userWalletList(auth()->id(), $request);
        });
    }

    /**
     * wallet deposit
     * @param $walletId
     * @return \Illuminate\Http\JsonResponse
     */
    public function walletDeposit($walletId)
    {
        try {
            $response = $this->service->userWalletDeposit(Auth::id(), $walletId);
        } catch (\Exception $e) {
            storeException('walletDeposit', $e->getMessage());
            $response = ['success' => false, 'message' => __('Something went wrong'), 'data' => []];
        }
        return response()->json($response);
    }

    /**
     * wallet withdrawal
     * @param $walletId
     * @return \Illuminate\Http\JsonResponse
     */
    public function walletWithdrawal($walletId)
    {
        try {
            $response = $this->service->userWalletWithdrawal(Auth::id(), $walletId);
        } catch (\Exception $e) {
            storeException('walletWithdrawal', $e->getMessage());
            $response = ['success' => false, 'message' => __('Something went wrong'), 'data' => []];
        }
        return response()->json($response);
    }

    /**
     * wallet withdrawal
     * @param WithdrawalRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function walletWithdrawalProcess(WithdrawalRequest $request)
    {
        try {
            $response = $this->transService->withdrawalProcess($request);
        } catch (\Exception $e) {
            storeException('walletWithdrawalProcess', $e->getMessage());
            $response = ['success' => false, 'message' => __('Something went wrong'), 'data' => []];
        }
        return response()->json($response);
    }

    public function walletHistoryApp(Request $request): JsonResponse
    {
        return $this->handlerApiResponse(function () use ($request): array {
            return $this->service->walletHistoryApp($request, auth()->id());
        });
    }

    public function coinSwapHistoryApp(Request $request): JsonResponse
    {
        return $this->handlerApiResponse(function () use ($request): array {
            return $this->service->coinSwapHistoryApp($request);
        });
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function coinSwapApp()
    {
        $data['title'] = __('Coin Swap');
        $data['wallets'] = Wallet::join('coins', 'coins.id', '=', 'wallets.coin_id')
            ->where(['wallets.user_id' => Auth::id(), 'wallets.type' => PERSONAL_WALLET, 'coins.status' => STATUS_ACTIVE])
            ->orderBy('wallets.id', 'ASC')
            ->select('wallets.*')
            ->get();
        return response()->json(['success' => true, 'data' => $data, 'message' => __('Coin Swap Data')]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * get rate of coin
     */
    public function getRateApp(WalletRateRequest $request)
    {
        $data = $this->service->get_wallet_rate($request);
        return response()->json($data);
    }

    /**
     * @param CoinSwapRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function swapCoinApp(CoinSwapRequest $request)
    {
        try {
            $data['success'] = false;
            $data['message'] = __('Something went wrong');
            $fromWallet = Wallet::where(['id' => $request->from_coin_id])->first();
            // if(isset($request->code)){
            //     $response = checkTwoFactor("two_factor_swap",$request);
            //     if(!$response["success"]){
            //         return response()->json($response);
            //     }
            // }
            if (!empty($fromWallet) && $fromWallet->type == CO_WALLET) {
                return response()->json($data);
            }
            $response = $this->service->get_wallet_rate($request);
            if ($response['success'] == false) {
                return response()->json($data);
            }
            $swap_coin = $this->service->coinSwap($response['from_wallet'], $response['to_wallet'], $response['convert_rate'], $response['amount'], $response['rate']);
            if ($swap_coin['success'] == true) {
                $data['success'] = true;
                $data['message'] = $swap_coin['message'];
            } else {
                $data['success'] = false;
                $data['message'] = $swap_coin['message'];
            }
            return response()->json($data);
        } catch (\Exception $e) {
            storeException('swapCoinApp ', $e->getMessage());
            return response()->json(responseData(false, __("Something went wrong")));
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCoinSwapDetailsApp(Request $request)
    {
        $wallet = Wallet::find($request->id);
        $data['wallets'] = Coin::select('coins.*', 'wallets.name as wallet_name', 'wallets.id as wallet_id', 'wallets.balance')
            ->join('wallets', 'wallets.coin_type', '=', 'coins.coin_type')
            ->where('coins.status', STATUS_ACTIVE)
            ->where('wallets.user_id', Auth::id())
            ->where('coins.coin_type', '!=', $wallet->coin_type)
            ->get();

        return response()->json($data);
    }

    /**
     * wallet network address
     * @param $walletId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getWalletNetworkAddress(NetworkAddressRequest $request)
    {
        try {
            $response = $this->service->getWalletNetworkAddress($request, Auth::id());
        } catch (\Exception $e) {
            storeException('getWalletNetworkAddress', $e->getMessage());
            $response = responseData(false);
        }
        return response()->json($response);
    }

    public function preWithdrawalProcess(Request $request)
    {
        try {
            $response = $this->transService->preWithdrawalProcess($request);
        } catch (\Exception $e) {
            storeException('walletWithdrawalProcess', $e->getMessage());
            $response = ['success' => false, 'message' => __('Something went wrong'), 'data' => []];
        }
        return response()->json($response);
    }

    public function getWalletBalanceDetails(Request $request)
    {
        return $this->handlerApiResponse(function () use ($request) {
            return $this->service->getWalletBalanceDetails($request);
        });
    }

    public function walletTotalValue(): JsonResponse
    {
        return $this->handlerApiResponse(function () {
            return $this->service->userWalletTotalValue(auth()->id());
        });
    }
}

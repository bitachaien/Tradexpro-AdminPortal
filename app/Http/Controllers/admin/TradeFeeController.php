<?php

namespace App\Http\Controllers\admin;

use App\Contracts\Repositories\TradeFeeRepositoryInterface;
use App\Dtos\TradeFeeInsertDto;
use App\Dtos\TradeFeeUpdateDto;
use App\Exceptions\CustomException;
use App\Http\Controllers\Controller;
use App\Http\Requests\TradeFeeCreationRequest;
use App\Http\Requests\TradeFeeUpdateRequest;
use App\Services\TradeSettingServices\TradeFeeService;
use Carbon\Carbon;
use Exception;
use Throwable;

class TradeFeeController extends Controller
{
    public function __construct(
        private TradeFeeRepositoryInterface $tradeFeeRepository
    ) {}

    public function index()
    {
        $query = $this->tradeFeeRepository->getQuery();

        return datatables()->of($query)
            ->editColumn('created_at', function ($item) {
                return Carbon::parse($item->created_at)->format('d M Y g:i:s A');
            })
            ->addColumn('user_email', function ($item) {
                return $item->user_email ? $item->user_email : 'N/A';
            })
            ->filterColumn('user_email', function ($query, $keyword) {
                $query->whereRaw('LOWER(users.email) LIKE ?', ["%{$keyword}%"]);
            })
            ->filterColumn('parent_coin', function ($query, $keyword) {
                $query->whereRaw('LOWER(parent_coins.coin_type) LIKE ?', [strtolower("%{$keyword}%")]);

            })
            ->filterColumn('child_coin', function ($query, $keyword) {
                $query->whereRaw('LOWER(child_coins.coin_type) LIKE ?', [strtolower("%{$keyword}%")]);
            })
            ->editColumn('status', function ($item) {
                return view('admin.exchange.trade.switch.fee_switch', compact('item'));
            })
            ->addColumn('actions', function ($item) {
                return view('admin.exchange.trade.switch.actions', compact('item'));
            })
            ->rawColumns(['created_at', 'status'])
            ->make(true);
    }

    public function changeStatus($id, TradeFeeService $tradeFeeService)
    {
        try {
            $id = decrypt($id);
            $tradeFeeService->changeStatus($id);

            return responseData(true, 'Trade fee status updated');
        } catch (Throwable $e) {
            storeException('Trade fee status', $e);

            return responseData(false, 'Something went wrong');
        }
    }

    public function store(TradeFeeCreationRequest $request, TradeFeeService $tradeFeeService)
    {
        try {
            $tradeFeeService->insert(TradeFeeInsertDto::fromRequest($request));

            return redirect()->back()->with(['success' => 'Successfully created']);
        } catch (CustomException $e) {
            storeException('Trade fee creation', $e);

            return redirect()->back()->with(['dismiss' => $e->getMessage()]);
        } catch (Throwable $e) {
            storeException('Trade fee creation', $e);

            return redirect()->back()->with(['dismiss' => 'Something went wrong']);
        }
    }

    public function update(
        $id,
        TradeFeeUpdateRequest $request,
        TradeFeeService $tradeFeeService
    ) {
        $id = decrypt($id);

        try {
            $tradeFeeService->update($id, TradeFeeUpdateDto::fromRequest($request));

            return redirect()->back()->with(['success' => 'Successfully updated']);
        } catch (CustomException $e) {
            storeException('Trade fee creation', $e);

            return redirect()->back()->with(['dismiss' => $e->getMessage()]);
        } catch (Throwable $e) {
            storeException('Trade fee creation', $e);

            return redirect()->back()->with(['dismiss' => 'Something went wrong']);
        }
    }
}

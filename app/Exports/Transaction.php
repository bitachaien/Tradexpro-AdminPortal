<?php
namespace App\Exports;

use App\User;
use App\Model\WithdrawHistory;
use App\Model\AffiliationHistory;
use App\Model\DepositeTransaction;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class Transaction implements FromCollection, WithHeadings
{
    public $type;
    public function __construct(
        public $request
    ){
        $this->type = $this->request->type ?? 'deposit';
    }
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        try{
            $data = null;
            $type = $this->request->type ?? 'deposit';
            if($type == 'deposit') $data = DepositeTransaction::select('address_type','sender_wallet_id','coin_type','address','receiver_wallet_id','amount','fees','created_at')
                                            ->with(['senderWallet.user','receiverWallet.user']);
            if($type == 'pending_deposit') $data = DepositeTransaction::select('sender_wallet_id','address','from_address','coin_type','network_type','amount','transaction_id','created_at')
                                            ->with(['senderWallet.user'])->where('status', STATUS_PENDING);
            if($type == 'withdrawal') $data = WithdrawHistory::select('address_type','user_id','coin_type','address','receiver_wallet_id','amount','fees','transaction_hash','status','created_at')
                                            ->with(['senderWallet.user','receiverWallet.user','user']);
            if($type == 'referral') {
                $data = AffiliationHistory::
                            join('users as reference_user', 'reference_user.id','=','affiliation_histories.user_id')
                            ->join('users as referral_user', 'referral_user.id','=','affiliation_histories.child_id')
                            ->latest()->select('affiliation_histories.transaction_id',
                            'reference_user.email as reference_user_email',
                            'referral_user.email as referral_user_email', 'affiliation_histories.coin_type', 'affiliation_histories.amount',
                            'affiliation_histories.created_at' );
            }
            if($type == 'pending_withdrawal_form'){
                $data = WithdrawHistory::select('address_type','user_id','coin_type','address','receiver_wallet_id','amount','transaction_hash','created_at')
                ->with(['senderWallet.user','receiverWallet.user','user'])->whereStatus(STATUS_PENDING);
            }
            if($type == 'reject_withdrawal_form'){
                $data = WithdrawHistory::select('address_type','user_id','coin_type','address','receiver_wallet_id','amount','fees','transaction_hash','created_at')
                ->with(['senderWallet.user','receiverWallet.user','user'])->whereStatus(STATUS_REJECTED);
            }
            if($type == 'active_withdrawal_form'){
                $data = WithdrawHistory::select('address_type','user_id','coin_type','address','receiver_wallet_id','amount','fees','transaction_hash','created_at')
                ->with(['senderWallet.user','receiverWallet.user','user'])->whereStatus(STATUS_ACCEPTED);
            }
            if($data == null) return collect();
            if(isset($this->request->from_date) && isset($this->request->to_date)){
                if($type == 'referral')
                $data = $data->whereBetween('affiliation_histories.created_at', [date('Y-m-d',strtotime($this->request->from_date)), date('Y-m-d',strtotime($this->request->to_date))]);
                else
                $data = $data->whereBetween('created_at', [date('Y-m-d',strtotime($this->request->from_date)), date('Y-m-d',strtotime($this->request->to_date))]);
            }
            $data = $data->get();
            // dd($data);
            $data->map(function($data) use($type){
                if($type == 'deposit'){
                    $data->address_type = ($data->address_type == 'internal_address') ? __('External') : addressType($data->address_type);
                    $data->sender_wallet_id = $data->senderWallet->user->first_name. ' ' . $data->senderWallet->user->last_name;
                    $data->receiver_wallet_id = $data->receiverWallet->user->first_name. ' ' . $data->receiverWallet->user->last_name;
                    $data->amount = $data->amount. ' ' . $data->coin_type;
                    $data->fees = $data->fees. ' ' . $data->coin_type;
                }
                if($type == 'pending_deposit'){
                    $data->sender_wallet_id = $data->senderWallet->user->first_name. ' ' . $data->senderWallet->user->last_name;
                    $data->amount = $data->amount. ' ' . $data->coin_type;
                }
                if($type == 'pending_withdrawal_form'){
                    $data->address_type = ($data->address_type == 'internal_address') ? __('External') : addressType($data->address_type);
                    $data->user_id = $data->user->first_name. ' ' . $data->user->last_name;
                    $data->receiver_wallet_id = $data->receiverWallet->user->first_name. ' ' . $data->receiverWallet->user->last_name;
                    $data->amount = $data->amount. ' ' . $data->coin_type;
                }
                if($type == 'reject_withdrawal_form' || $type == 'active_withdrawal_form'){
                    $data->address_type = ($data->address_type == 'internal_address') ? __('External') : addressType($data->address_type);
                    $data->user_id = $data->user->first_name. ' ' . $data->user->last_name;
                    $data->receiver_wallet_id = $data->receiverWallet->user->first_name. ' ' . $data->receiverWallet->user->last_name;
                    $data->amount = $data->amount. ' ' . $data->coin_type;
                    $data->fees = $data->fees. ' ' . $data->coin_type;
                }
                if($type == 'withdrawal'){
                    $data->address_type = ($data->address_type == 'internal_address') ? __('External') : addressType($data->address_type);
                    $data->user_id = $data->user->first_name. ' ' . $data->user->last_name;
                    $data->receiver_wallet_id = $data->receiverWallet->user->first_name. ' ' . $data->receiverWallet->user->last_name;
                    $data->amount = $data->amount. ' ' . $data->coin_type;
                    $data->fees = $data->fees. ' ' . $data->coin_type;
                    $data->status = deposit_status($data->status);
                }
            });
            return $data;
        }catch(\Exception $e)  {
            storeException('All Transaction Export', $e->getMessage());
            return collect();
        }
    }

    public function headings(): array
    {
        $deposit = [
            __("Type"), __("Sender"), __("Coin Type"), __("Address"),  __("Recevier"), __("Amount"), __("Fees"), __("Created At")
        ];
        
        $withdraw = [
            __("Type"), __("Sender"), __("Coin Type"), __("Address"),  __("Recevier"), __("Amount"), __("Fees"), __("Transaction Id"), __("Status") , __("Created At")
        ];
        
        $pending_deposit = [
            __("User"), __("Address"), __("From Address"), __("Coin Type"),  __("Coin Api"), __("Amount"), __("Transaction Id"), __("Created At")
        ];
        $referral = [
            __("Transaction Id"),__("User"), __("Referral By"), __("Coin"), __("Amount"), __("Created At")
        ];
        $pending_withdrawal_form = [
            __("Type"), __("Sender"), __("Coin Type"), __("Address"),  __("Recevier"), __("Amount"), __("Transaction Id"), __("Created At")
        ];
        $reject_withdrawal_form = [
            __("Type"), __("Sender"), __("Coin Type"), __("Address"),  __("Recevier"), __("Amount"), __("Fees"), __("Transaction Id"), __("Created At")
        ];
        if($this->type == 'deposit') return $deposit;
        if($this->type == 'pending_deposit') return $pending_deposit;
        if($this->type == 'referral') return $referral;
        if($this->type == 'pending_withdrawal_form') return $pending_withdrawal_form;
        if($this->type == 'reject_withdrawal_form' || $this->type == 'active_withdrawal_form') return $reject_withdrawal_form;
        return $withdraw;
    }
}
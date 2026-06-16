<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DepositeTransactionResource extends JsonResource
{
    public function toArray($request)
    {
        // return parent::toArray($request);
        return [
            'id' => $this->id,
            'address' => $this->address,
            'fees' => truncate_num($this->address),
            'sender_wallet_id' => $this->sender_wallet_id,
            'receiver_wallet_id' => $this->receiver_wallet_id,
            'address_type' => $this->address_type,
            'coin_type' => $this->coin_type,
            'network' => selected_node_network(@$this->receiverWallet->coin->network),
            'amount' => truncate_num($this->amount),
            'txId' => $this->transaction_id,
            'status' => $this->status,
            'confirmations' => $this->confirmations,
            'from_address' => $this->from_address,
            'updated_by' => $this->updated_by,
            'network_type' => $this->network_type,
            'is_admin_receive' => $this->is_admin_receive,
            'received_amount' => truncate_num($this->received_amount),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

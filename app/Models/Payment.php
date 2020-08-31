<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $primaryKey = 'iPaymentId';

    protected $fillable = ['tPaymentUserID', 'vPaymentUserStatus', 'ePaymentDriverStatus',
        'tPaymentDriverID ', 'iTripId', 'fCommision', 'iAmountUser', 'iAmountDriver'];

    public function trip()
    {
        return $this->hasOne(\App\Models\Trip::class, 'iTripId', 'iTripId');
    }
}
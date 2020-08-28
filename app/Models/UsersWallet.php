<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class UsersWallet extends Model
{
    protected $table = 'user_wallet';

    protected $primaryKey = 'iUserWalletId';

    protected $fillable = ['iUserId', 'eUserType', 'iBalance', 'eType',
        'iTripId', 'eFor', 'tDescription', 'ePaymentStatus', 'dDate',
        'fRatio_IRR', 'fRatio_USD'];


}
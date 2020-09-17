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

    protected $appends = ['eForText', 'eTypeText'];

    public function getEForTextAttribute()
    {
        switch ($this->attributes['eFor']) {
            case 'Deposit':
                return 'سپرده';
                break;
            case 'Booking':
                return 'هزینه سفر';
                break;
            case 'Refund':
                return 'بازپرداخت';
                break;
            case 'Withdrawl':
                return 'دریافت وجه';
                break;
            case 'Charges':
                return 'شارژ حساب';
                break;
            case 'Referrer':
                return 'معرفی کاربر';
                break;
        }
    }

    public function getETypeTextAttribute()
    {
        switch ($this->attributes['eType']) {
            case 'Credit':
                return 'واریز';
                break;
            case 'Debit':
                return 'برداشت';
                break;
        }
    }

}
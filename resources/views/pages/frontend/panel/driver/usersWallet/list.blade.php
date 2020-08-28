@extends('pages.frontend.panel.index')

@section('panel_content')
    <div class="col-12">
        <h2 class="text-right">کیف پول شما</h2>
    </div>
    <div class="row">
        <div class="col-12">
            <h3 class="text-center">
                موجودی کیف پول: {{addCurrencySymbol($walletAmount)}}
            </h3>
        </div>
    </div>
    <hr/>
    @foreach($wallets as $wallet)
        <div class="row m-1">
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-right">
                        <div class="row">
                            <div class="col-12">{{$wallet->tDescription}}</div>
                        </div>
                        <div class="row">
                            <div class="col-6 col-md-3"><span class="fa fa-money"></span> مبلغ
                                تراکنش {{addCurrencySymbol($wallet->iBalance)}}</div>
                            <div class="col-6 col-md-3"><span class="fa fa-cab"></span> شماره سفر {{$wallet->iTripId}}
                            </div>
                            <div class="col-6 col-md-3"><span class="fa fa-money"></span> علت پرداخت {{$wallet->eFor}}
                            </div>
                            <div class="col-6 col-md-3"><span class="fa fa-money"></span> نوع پرداخت {{$wallet->eType}}
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6"><span class="fa fa-money"></span> تاریخ تراکنش {{$wallet->dDate}}</div>
                            <div class="col-6"><span class="fa fa-cab"></span> مانده
                                حساب {{addCurrencySymbol($wallet->balance)}}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
    <h3>موجودی: {{addCurrencySymbol($wallet->balance)}}</h3>
@endsection
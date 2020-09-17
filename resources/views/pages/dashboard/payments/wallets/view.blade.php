@extends('pages.dashboard.payments.index')

@section('payments_content')
    <div class="row">
        <div class="col-12 col-md-3 text-center">
            <h5> نام کاربر: {{$user['fullName']}}</h5>
        </div>
        <div class="col-12 col-md-3 text-center">
            <h5>موجودی حساب: {{addCurrencySymbol($prevalence)}}</h5>
        </div>
        <div class="col-12 col-md-3 text-center">
            <button class="btn btn-primary" id="payToUserBtn">پرداخت وجه به کاربر</button>
        </div>
        <div class="col-12 col-md-3 text-center">
            <button class="btn btn-success">شارژ حساب کاربر</button>
        </div>
    </div>
    <div class="dropdown-divider"></div>
    <table class="table table-striped table-bordered table-hover">
        <tr>
            <th scope="col">ردیف</th>
            <th scope="col">نوع پرداخت</th>
            <th scope="col">علت پرداخت</th>
            <th scope="col">تاریخ</th>
            <th scope="col">مبلغ</th>
            <th scope="col">توضیحات</th>
            <th scope="col">شماره سفر</th>
            <th scope="col">مانده</th>
        </tr>
        @if(count($wallets) > 0)
            @php($i=1)
            @foreach($wallets as $wallet)
                <tr>
                    <td>{{$i}}</td>
                    <td class="{{$wallet->eType=='Credit' ? 'text-success' : 'text-danger'}}">{{$wallet->eTypeText}}</td>
                    <td>{{$wallet->eForText}}</td>
                    <td>{{$wallet->dDate}}</td>
                    <td class="{{$wallet->eType=='Credit' ? 'text-success' : 'text-danger'}}">{{addCurrencySymbol($wallet->iBalance)}}</td>
                    <td>{{$wallet->tDescription}}</td>
                    <td>{{$wallet->iTripId}}</td>
                    <td>{{addCurrencySymbol($wallet->balance)}}</td>
                </tr>
                @php($i++)
            @endforeach
        @else
            <tr>
                <td colspan="8"><h4>هیچ تراکنشی یافت نشد</h4></td>
            </tr>
        @endif
    </table>
    <div class="modal fade" id="payment-modal" tabindex="-1" role="dialog" aria-labelledby="payment-modalLabel"
         aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="payment-modalLabel">پرداخت وجه به کاربر</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="amount">مبلغ</label>
                        <input type="text" class="form-control" name="amount" id="amount">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary">پرداخت</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">انصراف</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('js')
    <script>
        $(document).ready(function () {
            $("#payToUserBtn").click(function () {
                $("#payment-modal").modal('toggle');
            });
        });
    </script>
@endpush
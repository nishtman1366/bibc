@extends('pages.dashboard.payments.index')

@section('payments_content')
    <div class="row">
        <div class="col-12">
            <table class="table table-striped table-bordered table-hover">
                <tr>
                    <th scope="col">ردیف</th>
                    <th scope="col">نام کاربر</th>
                    <th scope="col">موجودی کیف پول</th>
                    <th scope="col">عملیات</th>
                </tr>
                @if(count($list) > 0)
                    @php($i=1)
                    @foreach($list as $item)
                        <tr>
                            <td>{{$i}}</td>
                            <td>{{$item['name']}}</td>
                            <td>{{addCurrencySymbol($item['amount'])}}</td>
                            <td class="text-center">
                                <a href="{{url('dashboard.wallets.info',['userType'=>$item['type'],'userId'=>$item['id']])}}"><i
                                            class="fa fa-info-circle text-danger"
                                            data-toggle="tooltip"
                                            title="مشاهده جزئیات"></i></a>
                            </td>
                        </tr>
                        @php($i++)
                    @endforeach
                @else
                    <tr>
                        <td colspan="4">
                            <h3 class="text-center">هیچ اطلاعاتی یافت نشد</h3>
                        </td>
                    </tr>
                @endif
            </table>
            <div class="dropdown-divider"></div>
        </div>
    </div>
@endsection
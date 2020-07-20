@extends('pages.dashboard',['active'=>'feeSettings'])

@section('dashboard_content')
    <div class="col-12">
        <h2 class="text-right">تنظمیات نرخ</h2>
    </div>
    <hr/>
    <form action="{{url('feeSettings.update')}}" method="post">
        <div class="row">
            @foreach($items as $item)
                <div class="col-12 col-md-4 form-group">
                    <label for="setting_{{$item->id}}">{{$item->setting_name}}</label>
                    <input class="form-control" type="text" name="settings[{{$item->id}}]" value="{{$item->setting_value}}"
                           id="setting_{{$item->id}}">
                </div>
            @endforeach
        </div>
        <button class="btn btn-primary">ذخیره اطلاعات</button>
    </form>
@endsection
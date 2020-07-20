@extends('pages.dashboard',['active'=>'vehicleTypes'])

@section('dashboard_content')
    <div class="col-12">
        <h2 class="text-right">انواع خودرو</h2>
        <a class="btn btn-primary pull-left" href="{{url('vehicleTypes.new')}}">افزودن خودرو</a>
    </div>
    <hr/>
    <table class="table table-striped table-bordered table-hover">
        <thead>
        <tr>
            <th scope="col">نوع</th>
            <th scope="col">محدوده</th>
            <th scope="col">هزینه هر کیلومتر</th>
            <th scope="col">هزینه هر دقیقه</th>
            <th scope="col">کرایه پایه</th>
            <th scope="col">کمیسیون (%)</th>
            <th scope="col">ظرفیت</th>
            <th scope="col">نوع خودرو</th>
            <th scope="col">فعالیت</th>
        </tr>
        </thead>
        <tbody>
        @foreach($vehicleTypes as $vehicleType)
            <tr class="gradeA">
                <td>{{$vehicleType->vVehicleType}}</td>
                <td>{{$vehicleType->area->sAreaName}}</td>
                <td>{{$vehicleType->fPricePerKM}}</td>
                <td>{{$vehicleType->fPricePerMin}}</td>
                <td>{{$vehicleType->iBaseFare}}</td>
                <td>{{$vehicleType->fCommision}}</td>
                <td>{{$vehicleType->iPersonSize}}</td>
                <td>{{$vehicleType->eType}}</td>
                <td>
                    <a class="text-info"
                       href="{{url('vehicleTypes.edit',['id'=>$vehicleType->iVehicleTypeId])}}"
                       data-toggle="tooltip" title="ویرایش نوع خودرو">
                        <i class="fa fa-pencil"></i>
                    </a>
                    <a href="#" class="text-danger delete-vehicle-type" title="حذف نوع خودرو"
                       data-vehicle-type-id="{{$vehicleType['iVehicleTypeId']}}">
                        <i class="fa fa-trash"></i>
                    </a>
                    <form method="post"
                          action="{{url('vehicleTypes.delete',['id'=>$vehicleType->iVehicleTypeId])}}"
                          class="margin0">
                        <input type="hidden" name="_method" value="delete">
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection
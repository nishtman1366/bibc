@extends('pages.dashboard',['active'=>'vehicles'])

@section('dashboard_content')
    <div class="col-12">
        <h2 class="text-right">وسایل نقلیه</h2>
        <a class="btn btn-primary pull-left" href="{{url('vehicles.new')}}">افزودن سیله نقلیه</a>
    </div>
    <hr/>
    <table class="table table-striped table-bordered table-hover">
        <thead>
        <tr>
            <th scope="col" class="d-none"></th>
            <th scope="col">وسیله نقلیه</th>
            <th scope="col">شرکت</th>
            <th scope="col">راننده</th>
            <th scope="col">وضعیت</th>
            <th scope="col">عملیات</th>
        </tr>
        </thead>
        <tbody>
        @foreach($vehicles as $vehicle)
            <tr>
                <td>{{$vehicle->make->vMake . ' ' . $vehicle->model->vTitle}}</td>
                <td>{{$vehicle->company->vCompany}}</td>
                <td>{{$vehicle->driver->vName . ' ' . $vehicle->driver->vLastName}}</td>
                <td>
                    @if ($vehicle->eStatus == 'Active')
                        <i class="fa fa-check text-success"></i>
                    @elseif ($vehicle->eStatus == 'Inactive')
                        <i class="fa fa-ban text-secondary"></i>
                    @elseif ($vehicle->eStatus == 'Deleted')
                        <i class="fa fa-trash text-danger"></i>
                    @endif
                </td>
                <td>

                    <a class="text-primary" href="{{url('vehicles.edit',['id'=>$vehicle->iDriverVehicleId])}}"
                       data-toggle="tooltip" title="ویرایش وسیله نقلیه">
                        <i class="fa fa-pencil"></i>
                    </a>
                    @if($vehicle->eStatus!='Deleted')
                        <a class="text-info" href="{{url('documents',['model'=>'vehicles','modelId'=>$vehicle->iDriverVehicleId])}}"
                           data-toggle="tooltip" title="ویرایش اسناد">
                            <i class="fa fa-file"></i>
                        </a>
                        <a class="text-danger delete" href="#"
                           data-vehicle-company-name="{{$vehicle->company->vCompany}}"
                           data-vehicle-driver-name="{{$vehicle->driver->vName . ' ' . $vehicle->driver->vLastName}}"
                           data-toggle="tooltip"
                           title="حذف وسیله نقلیه">
                            <i class="fa fa-trash"></i>
                        </a>
                        <form method="post"
                              action="{{url('vehicles.delete',['id'=>$vehicle->iDriverVehicleId])}}"
                              class="margin0">
                            <input type="hidden" name="_method" value="delete">
                        </form>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection
@push('js')
    <script>
        $(document).ready(function () {
            $(".delete").click(function (e) {
                e.preventDefault();
                let form = $(this).next('form');
                let company = $(this).attr('data-vehicle-company-name');
                let driver = $(this).attr('data-vehicle-driver-name');
                if (confirm('آیا از حذف وسیله نقلیه شرکت ' + company + ' مطمئن هستید؟ به رانندگی ' + driver + '')) {
                    form.submit();
                } else {
                    return false;
                }
            });
        });
    </script>
@endpush
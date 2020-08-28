@extends('pages.frontend.panel.index')

@section('panel_content')
    <div class="col-12">
        <h2 class="text-right">خودروهای شما</h2>
        <a class="btn btn-primary" href="{{url('driver.vehicles.new')}}">افزودن خودرو</a>
    </div>
    <hr/>
    @foreach($vehicles as $vehicle)
        <div class="row m-1">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 col-md-3">
                                <h3>{{$vehicle->make->vMake . ' ' . $vehicle->model->vTitle}}</h3>
                                <h4>{{$vehicle->company->vCompany}}</h4>
                                <h5>{{$vehicle->company->vCompany}}</h5>
                            </div>
                            <div class="col-12 col-md-3">
                                <h4>{{$vehicle->driver->vName . ' ' . $vehicle->driver->vLastName}}</h4>
                            </div>
                            <div class="col-12 col-md-3">
                                @if ($vehicle->eStatus == 'Active')
                                    <i class="fa fa-check text-success"></i>
                                @elseif ($vehicle->eStatus == 'Inactive')
                                    <i class="fa fa-ban text-secondary"></i>
                                @elseif ($vehicle->eStatus == 'Deleted')
                                    <i class="fa fa-trash text-danger"></i>
                                @endif
                            </div>
                            <div class="col-12 col-md-3">
                                <a class="text-primary"
                                   href="{{url('driver.vehicles.edit',['id'=>$vehicle->iDriverVehicleId])}}"
                                   data-toggle="tooltip" title="ویرایش وسیله نقلیه">
                                    <i class="fa fa-pencil"></i>
                                </a>
                                @if($vehicle->eStatus!='Deleted')
                                    <a class="text-info"
                                       href="{{url('documents',['model'=>'vehicles','modelId'=>$vehicle->iDriverVehicleId])}}"
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
                                          action="{{url('driver.vehicles.delete',['id'=>$vehicle->iDriverVehicleId])}}"
                                          class="margin0">
                                        <input type="hidden" name="_method" value="delete">
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
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
@extends('pages.frontend.panel.index')

@php
    if(is_null($vehicle)){
        $action = url('driver.vehicles.create');
    }else{
        $action = url('driver.vehicles.update',['id'=>$vehicle->iDriverVehicleId]);
    }
@endphp
@section('panel_content')
    <form id="vehicle-form" method="post" action="{{$action}}">
        @if(!is_null($vehicle))
            <input type="hidden" id="u_id" name="id" value="{{$vehicle->iDriverVehicleId}}"/>
        @endif
        <div class="row">
            <div class="form-group col-12 col-md-2">
                <label for="iMakeId">خودرو<span class="red"> *</span></label>
                <select name="iMakeId" id="iMakeId" class="form-control" required>
                    <option value="">انتخاب خودرو</option>
                    @foreach($makes as $make)
                        <option value="{{$make->iMakeId}}"
                                {{(!is_null($vehicle) && $vehicle->iMakeId == $make->iMakeId) ? 'selected' : ''}}>{{$make->vMake}}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-12 col-md-2">
                <label for="iModelId">مدل<span class="red"> *</span></label>
                <div>
                    <select name="iModelId" id="iModelId" class="form-control" required>
                        <option value="">انتخاب مدل خودرو</option>
                        @if(!is_null($vehicle))
                            @foreach($models as $model)
                                <option value="{{$model->iModelId}}"
                                        {{(!is_null($vehicle) && $vehicle->iModelId == $model->iModelId) ? 'selected' : ''}}>{{$model->vTitle}}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>
            <div class="form-group col-12 col-md-2">
                <label for="iColor">رنگ<span class="red"> *</span></label>
                <input type="text" class="form-control" name="iColor" id="iColor"
                       value="{{!is_null($vehicle) ? $vehicle->iColor : ''}}"/>
            </div>
            <div class="form-group col-12 col-md-2">
                <label for="iYear">سال<span class="red"> *</span></label>
                <select name="iYear" id="iYear" class="form-control" required>
                    <option value="">انتخاب سال</option>
                    @for($j = jdate('Y')->format('Y'); $j >= 1370; $j--)
                        <option value="{{$j}}"
                                {{(!is_null($vehicle) && $vehicle->iYear == $j) ? 'selected' : ''}}>{{$j}}</option>
                    @endfor
                </select>
            </div>
            <div class="col-12 col-md-4 d-flex align-items-center">
                <div class="input-group">
                    <span class="input-group-text border-left-0" style="border-radius: 0;">پلاک خودرو:</span>
                    <input type="text" class="form-control" name="vLicencePlate_place1"
                           id="vLicencePlate_place1"
                           value="{{!is_null($vehicle) ? $vehicle->vLicencePlateDetail['vLicencePlate_place1'] : ''}}"
                           placeholder="00" required>
                    <input type="text" class="form-control" name="vLicencePlate_alphabet"
                           id="vLicencePlate_alphabet"
                           value="{{!is_null($vehicle) ? $vehicle->vLicencePlateDetail['vLicencePlate_alphabet'] : ''}}"
                           placeholder="الف"
                           required>
                    <input type="text" class="form-control" name="vLicencePlate_place2"
                           id="vLicencePlate_place2"
                           value="{{!is_null($vehicle) ? $vehicle->vLicencePlateDetail['vLicencePlate_place2'] : ''}}"
                           placeholder="000" required>

                    <input type="text" class="form-control" name="vLicencePlate_city"
                           id="vLicencePlate_city"
                           value="{{!is_null($vehicle) ? $vehicle->vLicencePlateDetail['vLicencePlate_city'] : ''}}"
                           placeholder="کد شهر" required>
                    <span class="text-danger" id="plate_warning"></span>
                    <input type="hidden" name="vLicencePlate" id="vLicencePlate" value="">
                    <input type="hidden" name="vLicencePlate_local" id="vLicencePlate_local" value="">
                </div>
            </div>
        </div>
        <div class="form-group">
            <label>نوع خودرو <span class="red"> *</span></label>
            <div class="alert alert-danger alert-dismissable" style="display:none;" id="car_error">
                <button class="close" type="button" id="cartypeClosed">×</button>
                شما باید حداقل یک نوع ماشین را انتخاب کنید
            </div>
            <div class="btn-group btn-group-toggle" data-toggle="buttons">
                @foreach ($vehicleTypes as $vehicleType)
                    <label class="btn btn-info">
                        <input type="checkbox" name="vCarType[]"
                               class="vehicle-type"
                               {{(!is_null($vehicle) && in_array($vehicleType->iVehicleTypeId, $vehicle->vCarTypeArray)) ? 'checked' : ''}}
                               autocomplete="off"
                               value="{{$vehicleType->iVehicleTypeId}}">{{$vehicleType->vVehicleType}}
                    </label>
                @endforeach
            </div>
        </div>
        <div class="form-group">
            <label>وضعیت</label>
            <div class="btn-group btn-group-toggle" data-toggle="buttons">
                <label class="btn btn-secondary {{(!is_null($vehicle) && $vehicle->eStatus=='Inactive') ? 'active' : ''}}">
                    <input type="radio" name="eStatus" id="option1"
                           autocomplete="off"
                           value="Active" {{(!is_null($vehicle) && $vehicle->eStatus=='Active') ? 'checked' : ''}}>
                    فعال
                </label>
                <label class="btn btn-secondary {{(!is_null($vehicle) && $vehicle->eStatus=='Inactive') ? 'active' : ''}}">
                    <input type="radio" name="eStatus" id="option2"
                           autocomplete="off"
                           value="Inactive" {{(!is_null($vehicle) && $vehicle->eStatus=='Inactive') ? 'checked' : ''}}>
                    غیرفعال
                </label>
            </div>
        </div>
        <a href="#" class="btn btn-info"
           id="submit-form">ثبت اطلاعات
        </a>
    </form>
@endsection
@push('js')
    <script>
        $(document).ready(function () {
            $("#iMakeId").change(function () {
                let makeId = $(this).val();
                let request = $.ajax({
                    type: "GET",
                    url: '/bibc/api/makes/' + makeId + '/models',
                    success: function (data) {
                        console.log(data);
                        $("#iModelId").children().remove();
                        if (data.length > 0) {
                            $.each(data, function (key, value) {
                                $("#iModelId").append($('<option>', {value: value.iModelId})
                                    .text(value.vTitle));
                            });
                        }
                    }
                });
                request.fail(function (jqXHR, textStatus) {
                    console.log("Request failed: " + textStatus);
                });
            });

            $("#submit-form").click(function (e) {
                e.preventDefault();
                let number = 'IRAN'
                    + '|' + $("#vLicencePlate_place1").val()
                    + '|' + $("#vLicencePlate_alphabet").val()
                    + '|' + $("#vLicencePlate_place2").val()
                    + '|' + $("#vLicencePlate_city").val() + '';
                let request = $.ajax({
                    type: "POST",
                    data: {number},
                    url: '/bibc/api/vehicles/checkLicense',
                    success: function (data) {
                        $("#vLicencePlate").val(number);
                        $("#vLicencePlate_local").val(number);
                        $("#vehicle-form").submit();
                    }
                });
                request.fail(function (jqXHR, textStatus) {
                    console.log(jqXHR.responseJSON.message);
                    if (jqXHR.status === 422) {
                        $("#plate_warning").text(jqXHR.responseJSON.message);
                    }
                });
            });
        });
    </script>
@endpush
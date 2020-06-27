@extends('pages.dashboard')
@php
    if(is_null($user)){
        $action = url('users.create');
    }else{
        $action = url('users.update',['id'=>$user['iAdminId']]);
    }
@endphp
@section('dashboard_content')
    <form method="post" action="{{$action}}">
        @if(!is_null($user))
            <input type="hidden" name="iAdminId" value="{{$user['iAdminId']}}"/>
            <input type="hidden" name="_method" value="PUT"/>
        @endif
        <div class="row">
            <div class="form-group col-12 col-md-4">
                <label for="vName">نام<span class="red"> *</span></label>
                <input type="text" pattern="[\D]+" class="form-control" name="vFirstName" id="vName"
                       value="{{!is_null($user) ? $user['vFirstName'] : ''}}" placeholder="First Name" required>
            </div>
            <div class="form-group col-12 col-md-4">
                <label for="vLastName">نام خانوادگی<span class="red"> *</span></label>
                <input type="text" pattern="[\D]+" class="form-control" name="vLastName" id="vLastName"
                       value="{{!is_null($user) ? $user['vLastName'] : ''}}" placeholder="Last Name" required>
            </div>
            <div class="form-group col-12 col-md-4">
                <label for="vContactNo">تلفن تماس<span class="red"> *</span></label>
                <input type="text" pattern="[0-9]{1,}" class="form-control" name="vPhone"
                       id="vContactNo" value="{{!is_null($user) ? $user['vPhone'] : ''}}" placeholder="Phone" required>
            </div>
        </div>
        <div class="row">
            <div class="form-group col-12 col-md-4">
                <label for="vEmail">آدرس ایمیل<span class="red"> *</span></label>
                <input type="email" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$"
                       class="form-control" name="vEmail" id="vEmail" value="{{!is_null($user) ? $user['vEmail'] : ''}}"
                       placeholder="Email" required>
                <div id="emailCheck"></div>
            </div>
            <div class="form-group col-12 col-md-4">
                <label for="vPassword">کلمه عبور<span class="red"> *</span></label>
                <input type="password" pattern=".{6,}" class="form-control" name="vPassword"
                       id="vPassword" value="{{!is_null($user) ? $user['vPassword'] : ''}}" placeholder="Password Label"
                       title="Six or more characters" required>
            </div>
            {{--        @if ($_SESSION['sess_iGroupId'] == 1)--}}
            <div class="form-group col-12 col-md-4">
                <label for="iGroupId">گروه کاربری<span class="red"> *</span><i class="icon-question-sign"
                                                                               data-placement="top"
                                                                               data-toggle="tooltip"
                                                                               data-original-title='Admin Group has 3 types. 1) Super Administrator - He can manage whole admin panel. 2) Dispatcher Administrator - He can manage Manual Taxi Dispatch. 3) Billing Administrator - He can see rides and details of each ride.'></i></label>
                <select class="form-control" name='iGroupId' id="iGroupId" required>
                    <option value="">انتخاب کنید:</option>
                    @foreach($groups as $group)
                        <option value="{{$group['iGroupId']}}" {{(!is_null($user) && $group['iGroupId'] == $user['iGroupId']) ? 'selected' : ''}}>
                            {{$group['vGroup']}}</option>
                    @endforeach
                </select>
            </div>
            {{--        @endif--}}
        </div>
        <div class="row">
            <div class="form-group col-12 col-md-4">
                <label for="area">Area<span class="red"> *</span></label>
                <select class="form-control" name="area" id="area" required>
                    <option value="-1">All</option>
                    @foreach($areas as $area)
                        <option {{ ((!is_null($user) && $area['aId'] == $user['adminArea']) ? 'selected ' : '')}} value="{{$area['aId']}}">{{$area['sAreaNamePersian']}}
                            - {{$area['sAreaName']}}</option>
                    @endforeach
                </select>
            </div>
            {{--        <div class="form-group">--}}
            {{--            <label>Accesses <?php echo $vAccessOptions; ?> <span class="red"> </span></label>--}}
            {{--            <!--                    <div class="btn-group btn-group-toggle" data-toggle="buttons">-->--}}
            {{--            <label class="btn btn-outline-warning">--}}
            {{--                <input type="checkbox" name="access[]"--}}
            {{--                       value="a0" <?php echo((strpos($vAccessOptions, 'a0') !== false) ? 'checked' : ''); ?>>داشبورد--}}
            {{--            </label>--}}
            {{--            <label class="btn btn-outline-warning">--}}
            {{--                <input type="checkbox" name="access[]"--}}
            {{--                       value="a1" <?php echo((strpos($vAccessOptions, 'a1') !== false) ? 'checked' : ''); ?>>ادمین--}}
            {{--                ها</label>--}}
            {{--            <label class="btn btn-outline-warning">--}}
            {{--                <input type="checkbox" name="access[]"--}}
            {{--                       value="2a" <?php echo (strpos($vAccessOptions, '2a') !== false && $vAccessOptions[strpos($vAccessOptions, '2a') - 1] != '1') ? 'checked' : ''; ?>>شرکت--}}
            {{--                ها</label>--}}
            {{--            <label class="btn btn-outline-warning">--}}
            {{--                <input type="checkbox" name="access[]"--}}
            {{--                       value="3a" <?php echo (strpos($vAccessOptions, '3a') !== false && $vAccessOptions[strpos($vAccessOptions, '3a') - 1] != '1') ? 'checked' : ''; ?>>ناحیه</label>--}}
            {{--            <label class="btn btn-outline-warning">--}}
            {{--                <input type="checkbox" name="access[]"--}}
            {{--                       value="4a" <?php echo (strpos($vAccessOptions, '4a') !== false && $vAccessOptions[strpos($vAccessOptions, '4a') - 1] != '1') ? 'checked' : ''; ?>>رانندگان</label>--}}
            {{--            <label class="btn btn-outline-warning">--}}
            {{--                <input type="checkbox" name="access[]"--}}
            {{--                       value="5a" <?php echo (strpos($vAccessOptions, '5a') !== false && $vAccessOptions[strpos($vAccessOptions, '5a') - 1] != '1') ? 'checked' : ''; ?>>حداکثر--}}
            {{--                بدهی</label>--}}
            {{--            <label class="btn btn-outline-warning">--}}
            {{--                <input type="checkbox" name="access[]"--}}
            {{--                       value="6a" <?php echo (strpos($vAccessOptions, '6a') !== false && $vAccessOptions[strpos($vAccessOptions, '6a') - 1] != '1') ? 'checked' : ''; ?>>ماشین</label>--}}
            {{--            <label class="btn btn-outline-warning">--}}
            {{--                <input type="checkbox" name="access[]"--}}
            {{--                       value="7a" <?php echo (strpos($vAccessOptions, '7a') !== false && $vAccessOptions[strpos($vAccessOptions, '7a') - 1] != '1') ? 'checked' : ''; ?>>نوع--}}
            {{--                ماشین</label>--}}
            {{--            <label class="btn btn-outline-warning">--}}
            {{--                <input type="checkbox" name="access[]"--}}
            {{--                       value="a2" <?php echo (strpos($vAccessOptions, 'a2') !== false && $vAccessOptions[strpos($vAccessOptions, 'a2') - 1] != '1') ? 'checked' : ''; ?>>تنظیمات--}}
            {{--                نرخ کلی</label>--}}
            {{--            <label class="btn btn-outline-warning">--}}
            {{--                <input type="checkbox" name="access[]"--}}
            {{--                       value="8a" <?php echo (strpos($vAccessOptions, '8a') !== false && $vAccessOptions[strpos($vAccessOptions, '8a') - 1] != '1') ? 'checked' : ''; ?>>نوع--}}
            {{--                بسته</label>--}}
            {{--            <label class="btn btn-outline-warning">--}}
            {{--                <input type="checkbox" name="access[]"--}}
            {{--                       value="9a" <?php echo (strpos($vAccessOptions, '9a') !== false && $vAccessOptions[strpos($vAccessOptions, '9a') - 1] != '1') ? 'checked' : ''; ?>>مسافران</label>--}}
            {{--            <label class="btn btn-outline-warning">--}}
            {{--                <input type="checkbox" name="access[]"--}}
            {{--                       value="10a" <?php echo (strpos($vAccessOptions, '10a') !== false) ? 'checked' : ''; ?>>رزرو</label>--}}
            {{--            <label class="btn btn-outline-warning">--}}
            {{--                <input type="checkbox" name="access[]"--}}
            {{--                       value="11a" <?php echo (strpos($vAccessOptions, '11a') !== false) ? 'checked' : ''; ?>>سفرها</label>--}}
            {{--            <label class="btn btn-outline-warning">--}}
            {{--                <input type="checkbox" name="access[]"--}}
            {{--                       value="12a" <?php echo (strpos($vAccessOptions, '12a') !== false) ? 'checked' : ''; ?>>رزروها</label>--}}
            {{--            <label class="btn btn-outline-warning">--}}
            {{--                <input type="checkbox" name="access[]"--}}
            {{--                       value="13a" <?php echo (strpos($vAccessOptions, '13a') !== false) ? 'checked' : ''; ?>>کد--}}
            {{--                تخفیف</label>--}}
            {{--            <label class="btn btn-outline-warning">--}}
            {{--                <input type="checkbox" name="access[]"--}}
            {{--                       value="14a" <?php echo (strpos($vAccessOptions, '14a') !== false) ? 'checked' : ''; ?>>معرفی--}}
            {{--                دوستان</label>--}}
            {{--            <label class="btn btn-outline-warning">--}}
            {{--                <input type="checkbox" name="access[]"--}}
            {{--                       value="15a" <?php echo (strpos($vAccessOptions, '15a') !== false) ? 'checked' : ''; ?>>گادز--}}
            {{--                ویو</label>--}}
            {{--            <label class="btn btn-outline-warning">--}}
            {{--                <input type="checkbox" name="access[]"--}}
            {{--                       value="16a" <?php echo (strpos($vAccessOptions, '16a') !== false) ? 'checked' : ''; ?>>نقشه--}}
            {{--                پراکندگی</label>--}}
            {{--            <label class="btn btn-outline-warning">--}}
            {{--                <input type="checkbox" name="access[]"--}}
            {{--                       value="17a" <?php echo (strpos($vAccessOptions, '17a') !== false) ? 'checked' : ''; ?>>نظرات</label>--}}
            {{--            <label class="btn btn-outline-warning">--}}
            {{--                <input type="checkbox" name="access[]"--}}
            {{--                       value="18a" <?php echo (strpos($vAccessOptions, '18a') !== false) ? 'checked' : ''; ?>>ناتیفیکیشن</label>--}}
            {{--            <label class="btn btn-outline-warning">--}}
            {{--                <input type="checkbox" name="access[]"--}}
            {{--                       value="19a" <?php echo (strpos($vAccessOptions, '19a') !== false) ? 'checked' : ''; ?>>گزارشات</label>--}}
            {{--            <label class="btn btn-outline-warning">--}}
            {{--                <input type="checkbox" name="access[]"--}}
            {{--                       value="20a" <?php echo (strpos($vAccessOptions, '20a') !== false) ? 'checked' : ''; ?>>تنظیمات</label>--}}
            {{--            <!--                    </div>-->--}}
            {{--        </div>--}}
        </div>
        <input type="submit" class="btn btn-primary col-12" name="submit" id="submit"
               value="ذخیره اطلاعات">
    </form>
@endsection
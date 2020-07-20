@extends('pages.dashboard',['active'=>'drivers'])
@php
    if(is_null($driver)){
        $action = url('drivers.create');
    }else{
        $action = url('drivers.update',['id'=>$driver['iDriverId']]);
    }
@endphp
@section('dashboard_content')
    <form method="post" action="{{$action}}" enctype="multipart/form-data">
        @if(!is_null($driver))
            <input type="hidden" id="u_id" name="id" value="{{$driver['iDriverId']}}"/>
        @endif
        <input type="hidden" id="usertype" name="usertype" value="driver"/>
        <div class="row">
            <div class="col-12 col-md-4">
                @if(!is_null($driver))
                    @if($driver['vImage']=='NONE' || $driver['vImage'])
                        <img class="m-auto" src="../assets/img/profile-user-img.png" alt="">
                    @else
                        <img class="w-100 rounded border border-dark"
                             src="storage/{{$driver['iDriverId']}}/3_{{$driver['vImage']}}"/>
                    @endif
                @endif
                <div class="form-group">
                    <label for="vImage">تصویر پروفایل</label>
                    <input type="file" class="form-control" name="vImage" id="vImage"
                           placeholder="Name Label" style="padding-bottom: 39px;">
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="form-group">
                    <label for="vName">نام<span class="red"> *</span></label>
                    <input type="text" pattern="[\D]+" title="Only Alpha characters allowed in name."
                           class="form-control" name="vName" id="vName"
                           value="{{!is_null($driver) ? $driver['vName'] : ''}}"
                           placeholder="First Name" required oninvalid="window.scrollTo(0,0);">
                </div>
                <div class="form-group">
                    <label for="vEmail">ایمیل<span class="red"> *</span></label>
                    <input type="email" pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$"
                           class="form-control" name="vEmail" onchange="validate_email(this.value)"
                           id="vEmail" value="{{!is_null($driver) ? $driver['vEmail'] : ''}}" placeholder="Email"
                           required>
                    <div id="emailCheck"></div>
                </div>
                <div class="form-group">
                    <label for="vCountry">کشور<span class="red"> *</span></label>
                    <select class="form-control" name='vCountry' id='vCountry'
                            onChange="changeCode(this.value);"
                            required>
                        <option value="">انتخاب کنید:</option>
                        @foreach ($countries as $country)
                            <option value="{{$country['vCountryCode']}}"
                                    {{(!is_null($driver) && $driver['vCountry'] == $country['vCountryCode']) ? 'selected' : ''}}>{{$country['vCountry']}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="iCompanyId">شرکت<span class="red"> *</span></label>
                    <select class="form-control" name='iCompanyId' id='iCompanyId' required>
                        <option value="">انتخاب کنید:</option>
                        @foreach ($companies as $company) { ?>
                        <option value="{{$company['iCompanyId']}}" {{(!is_null($driver) && $company['iCompanyId'] == $driver['iCompanyId']) ? 'selected' : ''}}>
                            {{$company['vName'] . " " . $company['vLastName'] . " (" . $company['vCompany'] . ")"}}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="vLang">زبان<span class="red"> *</span></label>
                    <select class="form-control" name='vLang' id="vLang" required>
                        <option value="">انتخاب کنید:</option>
                        @foreach ($languages as $language)
                            <option value="{{$language['vCode']}}" {{(!is_null($driver) && $language['vCode'] == $driver['vLang']) ? 'selected' : ''}}>
                                {{$language['vTitle']}}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="form-group">
                    <label for="vLastName">نام خانوادگی<span class="red"> *</span></label>
                    <input type="text" pattern="[\D]+" title="Only Alpha characters allowed in name."
                           class="form-control" name="vLastName" id="vLastName"
                           value="{{!is_null($driver) ? $driver['vLastName'] : ''}}" placeholder="Last Name" required
                           oninvalid="window.scrollTo(0,0);">
                </div>
                <div class="form-group">
                    <label for="vPassword">رمز عبور <span class="red"> *</span></label>
                    <input type="text" pattern=".{6,}" title="Six or more characters" class="form-control"
                           name="vPassword" id="vPassword" value="{{!is_null($driver) ? $driver['vPass'] : ''}}"
                           placeholder="Password Label" required>
                </div>
                <div class="form-group">
                    <label for="vCity">شهر<span class="red"> *</span></label>
                    <input type="text" class="form-control" name="vCity" id="vCity"
                           value="{{!is_null($driver) ? $driver['vCity'] : ''}}" placeholder="شهر" required
                           oninvalid="window.scrollTo(0,0);">
                </div>
                <input type="hidden" class="form-select-2" id="code" name="vCode"
                       value="{{!is_null($driver) ? $driver['vCode'] : ''}}" required readonly/>
                <div class="form-group">
                    <label for="vPhone">شماره موبایل<span class="red"> *</span></label>
                    <div class="input-group">
                        <input type="text" pattern="[0-9]{1,}" title="Please enter proper mobile number."
                               class="form-control border-left-0" name="vPhone"
                               id="vPhone" value="{{!is_null($driver) ? $driver['vPhone'] : ''}}" placeholder="Phone"
                               required
                               style="direction: ltr">
                        <div class="input-group-addon border border-right-0 bg-secondary"
                             style="font-size: 11px;direction: ltr">
                            {{!is_null($driver) ? $driver['vCode'] : '+98'}}
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="vCurrencyDriver">واحد پول <span class="red"> *</span></label>
                    <select class="form-control" name='vCurrencyDriver' id="vCurrencyDriver" required>
                        <option value="">انتخاب کنید:</option>
                        @foreach ($currencies as $currency)
                            <option value="{{$currency['vName']}}"
                                    @if(!is_null($driver))
                                    @if($driver['vCurrencyDriver'] == $currency['vName'])
                                    selected
                                    @else
                                    @if($currency['eDefault'] == "Yes" && $driver['vCurrencyDriver'] == '')
                                    selected
                                    @endif
                                    @endif
                                    @endif
                            >{{$currency['vName']}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12 col-md-4">
                <div class="form-group">
                    <label for="vPaymentEmail">ایمیل پرداخت</label>
                    <input type="email" class="form-control" name="vPaymentEmail" id="vPaymentEmail"
                           value="{{!is_null($driver) ? $driver['vPaymentEmail'] : ''}}" placeholder="Payment Email">
                </div>
                <div class="form-group">
                    <label for="vBankAccountHolderName">نام دارنده حساب</label>
                    <input type="text" class="form-control" name="vBankAccountHolderName"
                           id="vBankAccountHolderName"
                           value="{{!is_null($driver) ? $driver['vBankAccountHolderName'] : ''}}"
                           placeholder="Account Holder Name">
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="form-group">
                    <label for="vAccountNumber">شماره حساب</label>
                    <input type="text" class="form-control" name="vAccountNumber" id="vAccountNumber"
                           value="{{!is_null($driver) ? $driver['vAccountNumber'] : ''}}" placeholder="Account Number">
                </div>
                <div class="form-group">
                    <label for="vBankName">نام بانک</label>
                    <input type="text" class="form-control" name="vBankName" id="vBankName"
                           value="{{!is_null($driver) ? $driver['vBankName'] : ''}}" placeholder="Name of Bank">
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="form-group">
                    <label for="vBIC_SWIFT_Code">BIC/SWIFT Code</label>
                    <input type="text" class="form-control" name="vBIC_SWIFT_Code" id="vBIC_SWIFT_Code"
                           value="{{!is_null($driver) ? $driver['vBIC_SWIFT_Code'] : ''}}" placeholder="BIC/SWIFT Code">
                </div>
                <div class="form-group">
                    <label for="vBankLocation">موقعیت بانک</label>
                    <input type="text" class="form-control" name="vBankLocation" id="vBankLocation"
                           value="{{!is_null($driver) ? $driver['vBankLocation'] : ''}}" placeholder="Bank Location">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="form-group col-12 col-md-6">
                <div class="btn-group btn-group-toggle" data-toggle="buttons">
                    <label class="btn btn-outline-warning {{((!is_null($driver) && $driver['eStatus']=='active') ? 'active' : '')}}">
                        <input type="radio" name="eStatus"
                               value="Active" {{((!is_null($driver) && $driver['eStatus']=='active') ? 'checked' : '')}}>فعال</label>
                    <label class="btn btn-outline-warning {{((!is_null($driver) && $driver['eStatus']=='inactive') ? 'active' : '')}}">
                        <input type="radio" name="eStatus"
                               value="Inactive" {{((!is_null($driver) && $driver['eStatus']=='inactive') ? 'checked' : '')}}>غیرفعال</label>
                    <label class="btn btn-outline-warning {{((!is_null($driver) && $driver['eStatus']=='deleted') ? 'active' : '')}}">
                        <input type="radio" name="eStatus"
                               value="Deleted" {{((!is_null($driver) && $driver['eStatus']=='deleted') ? 'checked' : '')}}>حذف
                        شده</label>
                </div>
            </div>
            <input type="submit" class="btn btn-primary" name="submit" id="submit"
                   value="ذخیره اطلاعات">
        </div>
    </form>
@endsection
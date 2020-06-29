@extends('pages.dashboard')
@php
    if(is_null($company)){
        $action = url('companies.create');
    }else{
        $action = url('companies.update',['id'=>$company['iCompanyId']]);
    }
@endphp
@section('dashboard_content')
    <form method="post" action="{{$action}}">
        <div class="row">
            <div class="col-12 col-md-4">
                @if(!is_null($company))
                    <input type="hidden" name="id" value="{{$company['iCompanyId']}}"/>
                @endif
                <div class="form-group">
                    <label for="vCompany">نام شرکت<span class="red"> *</span></label>
                    <input type="text" class="form-control" name="vCompany" id="vCompany"
                           value="{{!is_null($company) ? $company['vCompany'] : ''}}" placeholder="Company Name"
                           required>
                </div>

                <div class="form-group">
                    <label for="iCompanyCode">کد شرکت<span class="red"> *</span></label>
                    <input type="text" class="form-control" name="iCompanyCode" id="iCompanyCode"
                           value="{{!is_null($company) ? $company['iCompanyCode'] : ''}}" placeholder="Company Code"
                           required>
                </div>

                <div class="form-group">
                    <label for="iParentId">والدین <span class="red"> *</span></label>
                    <select class="form-control" name="iParentId" id="iParentId" onChange="" required>
                        <option value="0">--select--</option>
                        @foreach ($companies as $item)
                            <option value="{{$item->iCompanyId}}" {{ (!is_null($company) && $company->iParentId==$item->iCompanyId) ? 'selected' : ''}}>
                                {{$item->vCompany}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="iAreaId">محدوده <span class="red"> *</span></label>
                    <select class="form-control" name="iAreaId" id="iAreaId" onChange="" required>
                        <option value="0">--select--</option>
                        @foreach($areas as $area)
                            <option value="{{ $area->aId}}"
                                    {{(!is_null($company) && $company->iAreaId == $area->aId) ? 'selected' : '' }}>
                                {{$area->sAreaName}}
                                ({{$area->sAreaNamePersian}})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="vEmail">ایمیل<span class="red"> *</span></label>
                    <input type="email" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$" class="form-control"
                           name="vEmail" id="vEmail" value="{{!is_null($company) ? $company->vEmail : ''}}"
                           placeholder="Email" required
                           onChange="validate_email(this.value,{{!is_null($company) ? $company->iCompanyId : ''}}"/>
                    <div id="emailCheck"></div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="form-group">
                    <label for="vPassword">رمز عبور<span class="red"> *</span></label>
                    <input type="password" pattern=".{6,}" title="Six or more characters" class="form-control"
                           name="vPassword" id="vPassword"
                           value="{{!is_null($company) ? decrypt($company->vPassword) : ''}}"
                           placeholder="Password"
                           required>
                </div>
                <div class="form-group">
                    <label for="vManagerPassword">رمز عبور مدیر<span class="red"> *</span></label>
                    <input type="password" pattern=".{6,}" title="Six or more characters" class="form-control"
                           name="vManagerPassword" id="vManagerPassword"
                           value="{{!is_null($company) ? decrypt($company->vManagerPassword) : ''}}"
                           placeholder="Manager Password" required>
                </div>
                <div class="form-group">
                    <label for="vPhone">موبایل<span class="red"> *</span></label>
                    <input type="text" pattern="[0-9]{1,}" class="form-control" name="vPhone" id="vPhone"
                           value="{{!is_null($company) ? $company->vPhone : ''}}" placeholder="Phone"
                           title="Please enter proper mobile number." required>
                </div>
                <div class="form-group">
                    <label for="iPercentageShare">سهم درصد<span class="red"> *</span></label>
                    <input type="text" pattern="[0-9]{1,}" class="form-control" name="iPercentageShare"
                           id="iPercentageShare"
                           value="{{!is_null($company) ? $company->iPercentageShare : ''}}"
                           placeholder="Percentage Share"
                           title="Please enter company Percentage Share." required>
                </div>
                <div class="form-group">
                    <label for="vCountry">کشور <span class="red"> *</span></label>
                    <select class="form-control" name='vCountry' id="vCountry"
                            onChange="changeCode(this.value);"
                            required>
                        <option value="">--select--</option>
                        @foreach($countries as $country)
                            <option value="{{$country->vCountryCode}}"
                                    {{!is_null($company) && $company->vCountry==$country->vCountryCode ? 'selected' : ''}}>
                                {{$country->vCountry}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="form-group">
                    <label for="vCaddress">آدرس اول<span class="red"> *</span></label>
                    <input type="text" class="form-control" name="vCaddress" id="vCaddress"
                           value="{{!is_null($company) ? $company->vCaddress : ''}}" placeholder="Address Line 1"
                           required>
                </div>
                <div class="form-group">
                    <label for="vCadress2">آدرس دوم</label>
                    <input type="text" class="form-control" name="vCadress2" id="vCadress2"
                           value="{{!is_null($company) ? $company->vCadress2 : ''}}" placeholder="Address Line 2">
                </div>
                <div class="form-group">
                    <label for="vCity">شهر<span class="red"> *</span></label>
                    <input type="text" class="form-control" name="vCity" id="vCity"
                           value="{{!is_null($company) ? $company->vCity : ''}}"
                           placeholder="City" required>
                </div>
                <div class="form-group">
                    <label for="vVatNum">شماره وات</label>
                    <input type="text" class="form-control" name="vVatNum" id="vVatNum"
                           value="{{!is_null($company) ? $company->vVatNum : ''}}" placeholder="VAT Number">
                </div>
                <div class="form-group">
                    <div class="btn-group btn-group-toggle" data-toggle="buttons">
                        <label class="btn btn-outline-warning {{((!is_null($company) && $company['eStatus']=='Active') ? 'active' : '')}}">
                            <input type="radio" name="eStatus"
                                   value="Active" {{((!is_null($company) && $company['eStatus']=='Active') ? 'checked' : '')}}>فعال</label>
                        <label class="btn btn-outline-warning {{((!is_null($company) && $company['eStatus']=='Inactive') ? 'active' : '')}}">
                            <input type="radio" name="eStatus"
                                   value="Inactive" {{((!is_null($company) && $company['eStatus']=='Inactive') ? 'checked' : '')}}>غیرفعال</label>
                        <label class="btn btn-outline-warning {{((!is_null($company) && $company['eStatus']=='Deleted') ? 'active' : '')}}">
                            <input type="radio" name="eStatus"
                                   value="Deleted" {{((!is_null($company) && $company['eStatus']=='Deleted') ? 'checked' : '')}}>حذف
                            شده</label>
                    </div>
                </div>
            </div>
        </div>
        <input type="submit" class="btn btn-primary col-12" name="submit" id="submit"
               value="ذخیره اطلاعات">
    </form>
@endsection
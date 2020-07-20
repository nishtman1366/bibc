@extends('pages.dashboard')

@section('dashboard_content')
    <div class="col-12">
        <h2 class="text-right">شرکت ها</h2>
        <a class="btn btn-primary pull-left" href="{{url('companies.new')}}">افزودن شرکت</a>
    </div>
    <hr/>
    <table class="table table-striped table-bordered table-hover">
        <thead>
        <tr>
            <th scope="col">نام شرکت</th>
            <th scope="col">راننده</th>
            <th scope="col">محدوده</th>
            <th scope="col">موبایل</th>
            <th scope="col">تاریخ ثبت نام</th>
            <th scope="col">وضعیت</th>
            <th scope="col">ویرایش اسناد</th>
            <th scope="col">عملیات</th>
        </tr>
        </thead>
        <tbody>
        @foreach($companies as $company)
            <tr class="gradeA">
                <td>{{$company->vCompany}}</td>
                {{--                <td>{{$generalobjAdmin->clearEmail($user['vEmail'])}}</td>--}}
                <td><a href="{{url('companies.drivers',['id'=>$company['iCompanyId']])}}">{{$company['drivers_count']}}</a>
                </td>
                <td>{{$company->area['sAreaNamePersian']}}</td>
                {{--                <td>{{$generalobjAdmin->clearPhone($user['vContactNo'])}}</td>--}}
                <td>{{$company['vPhone']}}</td>
                <td>{{$company->date}}</td>
                <td>
                    @if($company['iCompanyId'] == 1)
                        <i class="fa fa-check text-success"></i>
                    @else
                        @if($company['eStatus'] == 'Active')
                            <i class="fa fa-check text-success"></i>
                        @elseif($company['eStatus'] == 'Inactive')
                            <i class="fa fa-ban text-secondary"></i>
                        @elseif($company['eStatus'] == 'Deleted')
                            <i class="fa fa-trash text-danger"></i>
                        @endif
                    @endif
                </td>
                <td>
                    <a href="{{url('documents',['model'=>'companies','modelId'=>$company['iCompanyId']])}}"><i
                                class="fa fa-file"></i></a>
                </td>
                <td>
                    <a class="text-primary"
                       href="{{url('companies.edit',['id'=>$company['iCompanyId']])}}"
                       data-toggle="tooltip" title="ویرایش">
                        <i class="fa fa-pencil"></i>
                    </a>
                    @if ($company['iCompanyId'] != 1)
                        <a class="text-danger" href="#"
                           onclick="$('#delete_form_{{$company['iCompanyId']}}').submit()"
                           data-toggle="tooltip" title="حذف">
                            <i class="fa fa-trash"></i>
                        </a>
                        <form name="delete_form" id="delete_form_{{$company['iCompanyId']}}"
                              method="post"
                              action="{{url('companies.delete',['id'=>$company['iCompanyId']])}}"
                              onsubmit="return confirm_delete()">
                            <input type="hidden" name="_method" value="DELETE"/>
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
        function confirm_delete() {
            return confirm("Are You sure You want to Delete Driver?");
        }
    </script>
@endpush
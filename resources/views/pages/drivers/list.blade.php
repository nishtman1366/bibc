@extends('pages.dashboard',['active'=>'drivers'])

@section('dashboard_content')
    <div class="col-12">
        <h2 class="text-right">رانندگان</h2>
        <a class="btn btn-primary pull-left" href="{{url('drivers.new')}}">افزودن راننده</a>
    </div>
    <hr/>
    <table class="table table-striped table-bordered table-hover">
        <thead>
        <tr>
            <th scope="col">نام راننده</th>
            <th scope="col">نام شرکت</th>
            <th scope="col">ایمیل</th>
            <th scope="col">تاریخ ثبت نام</th>
            <th scope="col">موبایل</th>
            <th scope="col">شهر</th>
            <th scope="col">وضعیت</th>
            <th scope="col">فعالیت</th>
        </tr>
        </thead>
        <tbody>
        @foreach($drivers as $driver)
            <tr class="gradeA">
                <td>{{$driver['vName'] . ' ' . $driver['vLastName']}}</td>
                <td>{{$driver['companyFirstName']}}</td>
                <td>{{$driver['vEmail']}}</td>
                <td data-order="{{$driver['iDriverId']}}">{{jdate(strtotime($driver['tRegistrationDate']))->format('Y-m-d')}}</td>
                <td>{{$driver['vPhone']}}</td>
                <td>{{$driver['vCity']}}</td>
                <td>
                    @if ($driver['eDefault'] != 'Yes')
                        @if($driver['eStatus'] == 'active')
                            <i class="fa fa-check text-success"></i>
                        @elseif($driver['eStatus'] == 'inactive')
                            <i class="fa fa-ban text-secondary"></i>
                        @elseif ($driver['eStatus'] == 'Deleted')
                            <i class="fa fa-trash text-danger"></i>
                        @else
                            <i class="fa fa-check text-success"></i>
                        @endif
                    @endif
                </td>
                <td>
                    <a href="{{url('drivers.edit',['id'=>$driver['iDriverId']])}}"
                       data-toggle="tooltip" title="ویرایش راننده">
                        <i class="fa fa-pencil text-primary"></i>
                    </a>
                    @if ($driver['eStatus'] != "Deleted")
                        <a href="{{url('documents',['model'=>'drivers','modelId'=>$driver['iDriverId']])}}"
                           data-toggle="tooltip" title="مدارک راننده">
                            <i class="fa fa-file"></i>
                        </a>
                        <a href="#" class="delete" data-toggle="tooltip"
                           title="حذف راننده">
                            <i class="fa fa-trash text-danger"></i>
                        </a>
                        <form method="post"
                              action="{{url('drivers.delete',['id'=>$driver['iDriverId']])}}"
                              onSubmit="return confirm('Are you sure you want to delete {{$driver['vName']}} {{$driver['vLastName']}} record?')"
                              class="margin0">
                            <input type="hidden" name="_method" value="DELETE"/>
                        </form>
                        <a href="#" class="reset-driver" data-toggle="tooltip"
                           title="بازنشانی راننده">
                            <i class="fa fa-refresh text-warning"></i>
                        </a>
                        <form method="post" action="{{url('drivers.reset',['id'=>$driver['iDriverId']])}}"
                              onSubmit="return confirm('Are you sure ? You want to reset {{$driver['vName']}} {{$driver['vLastName']}} account?')"
                              class="margin0">
                            <input type="hidden" name="action" id="action" value="reset">
                            <input type="hidden" name="res_id" id="res_id"
                                   value="{{$driver['iDriverId']}}">
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
            $(".delete").click(function () {
                let form = $(this).next('form');
                form.submit();
            });
            $(".reset-driver").click(function () {
                let form = $(this).next('form');
                form.submit();
            });
        });
    </script>
@endpush
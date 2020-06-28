@extends('pages.dashboard')

@section('dashboard_content')
    <div class="col-12">
        <h2 class="text-right">مدیران سیستم</h2>
            <a class="btn btn-primary pull-left" href="{{url('users.new')}}">افزودن ادمین</a>
    </div>
    <hr/>
    <table class="table table-striped table-bordered table-hover">
        <thead>
        <tr>
            <th scope="col">نام ادمین</th>
            <th scope="col">ایمیل</th>
            <th scope="col">نوع ادمین</th>
            <th scope="col">موبایل</th>
            <th scope="col">وضعیت</th>
            <th scope="col">عملیات</th>
        </tr>
        </thead>
        <tbody>
        @foreach($users as $user)
            <tr class="gradeA">
                <td>{{$user['vFirstName'] . ' ' . $user['vLastName']}}</td>
                {{--                <td>{{$generalobjAdmin->clearEmail($user['vEmail'])}}</td>--}}
                <td>{{$user['vEmail']}}</td>
                <td>{{$user['vGroup']}}</td>
                {{--                <td>{{$generalobjAdmin->clearPhone($user['vContactNo'])}}</td>--}}
                <td>{{$user['vContactNo']}}</td>
                <td>
                    @if($user['eDefault'] != 'Yes')
                        @if($user['eStatus'] == 'Active')
                            <i class="fa fa-check text-success"></i>
                        @elseif($user['eStatus'] == 'Inactive')
                            <i class="fa fa-ban text-secondary"></i>
                        @elseif($user['eStatus'] == 'Deleted')
                            <i class="fa fa-trash text-danger"></i>
                        @endif
                    @else
                        <i class="fa fa-check text-success"></i>
                    @endif
                </td>
                <td>
                    <a class="text-primary"
                       href="{{url('users.edit',['id'=>$user['iAdminId']])}}"
                       data-toggle="tooltip" title="ویرایش">
                        <i class="fa fa-pencil"></i>
                    </a>
                    <?php if ($user['eDefault'] != 'Yes') { ?>
                    {{--                    <a class="text-success"--}}
                    {{--                       href="{{url('users', ['iAdminId' => $user['iAdminId'], 'status' => 'Active'])}}"--}}
                    {{--                       data-toggle="tooltip" title="فعال سازی">--}}
                    {{--                        <i class="fa fa-check"></i>--}}
                    {{--                    </a>--}}
                    {{--                    <a class="text-secondary"--}}
                    {{--                       href="{{url('users', ['iAdminId' => $user['iAdminId'], 'status' => 'Inactive'])}}"--}}
                    {{--                       data-toggle="tooltip" title="غیرفعال سازی">--}}
                    {{--                        <i class="fa fa-ban"></i>--}}
                    {{--                    </a>--}}
                    <a class="text-danger" href="#"
                       onclick="$('#delete_form_{{$user['iAdminId']}}').submit()"
                       data-toggle="tooltip" title="حذف">
                        <i class="fa fa-trash"></i>
                    </a>
                    <form name="delete_form" id="delete_form_{{$user['iAdminId']}}"
                          method="post"
                          action="{{url('users.delete',['id'=>$user['iAdminId']])}}"
                          onsubmit="return confirm_delete()">
                        <input type="hidden" name="_method" value="DELETE"/>
                    </form>
                </td>
            </tr>
            <?php } ?>
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
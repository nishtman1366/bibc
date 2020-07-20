@extends('pages.dashboard',['active'=>'passengers'])

@section('dashboard_content')
    <div class="col-12">
        <h2 class="text-right">مسافران</h2>
        <a class="btn btn-primary pull-left" href="{{url('vehicles.new')}}">افزودن مسافر</a>
    </div>
    <hr/>
    <table class="table table-striped table-bordered table-hover">
        <thead>
        <tr>
            <th scope="col">نام مسافر</th>
            <th scope="col">آدرس ایمیل</th>
            <th scope="col">تاریخ ثبت نام</th>
            <th scope="col">وضعیت</th>
            <th scope="col">عملیات</th>
        </tr>
        </thead>
        <tbody>
        @foreach($passengers as $passenger)
            <tr>
                <td>{{$passenger->fullName}}</td>
                <td>{{$passenger->vEmail}}</td>
                <td>{{jdate($passenger->tRegistrationDate)->format('Y/m/d')}}</td>
                <td>
                    @if($passenger->eStatus=='Active')
                        <i class="fa fa-check text-success" data-toggle="tooltip" title="فعال"></i>
                    @elseif($passenger->eStatus=='Inactive')
                        <i class="fa fa-ban text-secondary" data-toggle="tooltip" title="غیرفعال"></i>
                    @else
                        <i class="fa fa-trash text-danger" data-toggle="tooltip" title="حذف شده"></i>
                    @endif
                </td>
                <td>
                    @if($passenger->eStatus!='Deleted')
                        <a class="text-primary" href="{{url('passengers.edit',['id'=>$passenger->iUserId])}}"
                           data-toggle="tooltip" title="ویرایش اطلاعات مسافر">
                            <i class="fa fa-pencil"></i>
                        </a>
                    @endif
                    <a class="text-danger delete" href="#"
                       data-toggle="tooltip"
                       title="حذف مسافر">
                        <i class="fa fa-trash"></i>
                    </a>
                    <form method="post" id="delete-passenger-{{$passenger->iUserId}}"
                          action="{{url('passengers.delete',['id'=>$passenger->iUserId])}}">
                        <input type="hidden" name="_method" value="delete">
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection
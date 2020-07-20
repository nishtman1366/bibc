@extends('pages.dashboard',['active'=>'packageTypes'])

@section('dashboard_content')
    <div class="col-12">
        <h2 class="text-right">انواع بسته ها</h2>
        <a class="btn btn-primary pull-left" href="{{url('vehicles.new')}}">افزودن نوع بسته</a>
    </div>
    <hr/>
    <table class="table table-striped table-bordered table-hover">
        <thead>
        <tr>
            <th scope="col">نام بسته</th>
            <th scope="col">وضعیت</th>
            <th scope="col">عملیات</th>
        </tr>
        </thead>
        <tbody>
        @foreach($packageTypes as $packageType)
            <tr>
                <td>{{$packageType->vName_PS}}</td>
                <td>
                    @if($packageType->eStatus=='Active')
                        <i class="fa fa-check text-success" data-toggle="tooltip" title="فعال"></i>
                    @else
                        <i class="fa fa-ban text-secondary" data-toggle="tooltip" title="غیرفعال"></i>
                    @endif
                </td>
                <td>
                    <a class="text-primary" href="{{url('packageTypes.edit',['id'=>$packageType->iPackageTypeId])}}"
                       data-toggle="tooltip" title="ویرایش نوع بسته">
                        <i class="fa fa-pencil"></i>
                    </a>
                    <a class="text-danger delete" href="#"
                       data-toggle="tooltip"
                       title="حذف نوع بسته">
                        <i class="fa fa-trash"></i>
                    </a>
                    <form method="post" id="delete-package-type-{{$packageType->iPackageTypeId}}"
                          action="{{url('packageTypes.delete',['id'=>$packageType->iPackageTypeId])}}">
                        <input type="hidden" name="_method" value="delete">
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection
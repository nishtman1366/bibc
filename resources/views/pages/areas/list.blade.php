@extends('pages.dashboard')

@section('dashboard_content')
    <div class="col-12">
        <h2 class="text-right">مدیران سیستم</h2>
        <a class="btn btn-primary pull-left" href="{{url('areas.new')}}">افزودن ادمین</a>
    </div>
    <hr/>
    <table class="table table-striped table-bordered table-hover">
        <thead>
        <tr>
            <th>نام ناحیه</th>
            <th>وضعیت</th>
            <th>عملیات</th>
        </tr>
        </thead>
        <tbody>
        @foreach($areas as $area)
            <tr class="gradeA">
                <td>{{$area['sAreaName']}} ({{$area['sAreaNamePersian']}})</td>
                <td>
                    @if($area['sActive'] == 'Yes')
                        <i class="fa fa-check text-success"></i>
                    @else
                        <i class="fa fa-ban text-secondary"></i>
                    @endif
                <td>
                    <a class="text-primary"
                       href="{{url('areas.edit',['id'=>$area['aId']])}}"
                       data-toggle="tooltip" title="ویرایش">
                        <i class="fa fa-pencil"></i>
                    </a>
                    <a class="text-danger" href="#"
                       onclick="$('#delete_form_{{$area['aId']}}').submit()"
                       data-toggle="tooltip" title="حذف">
                        <i class="fa fa-trash"></i>
                    </a>
                    <form name="delete_form" id="delete_form_{{$area['aId']}}"
                          method="post"
                          action="{{url('areas.delete',['id'=>$area['aId']])}}"
                          onsubmit="return confirm_delete()">
                        <input type="hidden" name="_method" value="DELETE"/>
                    </form>
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
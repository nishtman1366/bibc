<div id="sidebar" class="m-0 p-0 bg-dark position-fixed h-100">
    <div class="sidebar-header">
        <div class="user-pic">
            {{--            <img class="img-responsive img-rounded" src="<?php assets('images/icons/user.png'); ?>" alt="">--}}
            <img class="img-responsive img-rounded" src="{{assets('images/icons/user.png')}}" alt="">
        </div>
        <div class="user-info">
            {{--            <p><?php echo $_SESSION['sess_vAdminFirstName'] . " " . $_SESSION['sess_vAdminLastName']; ?></p>--}}
            <p>محسن میرحسینی</p>
            <a href="logout.php" style="font-size: .9rem">خروج از سیستم</a>
        </div>
    </div>
    <div class="sidebar-content">
        <ul>
            @foreach($sidebar['menu']['items'] as $item)
                <li>
                    <a href="{{$item['href']}}" {{((isset($active) && $active==$item['name']) ? 'class=text-info' : '')}}>
                        <i class="fa {{$item['icon']}}"></i>{{$item['title']}}</a>
                </li>
            @endforeach
        </ul>
    </div>
    <div class="sidebar-footer"></div>
</div>
<!--END MENU SECTION -->
@push('js')

@endpush
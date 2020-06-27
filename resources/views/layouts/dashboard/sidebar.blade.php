<div id="sidebar" class="m-0 p-0 bg-dark position-fixed h-100">
    <div class="sidebar-header">
        <div class="user-pic">
            {{--            <img class="img-responsive img-rounded" src="<?php assets('images/icons/user.png'); ?>" alt="">--}}
            <img class="img-responsive img-rounded" src="optimized/assets/images/icons/user.png" alt="">
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
                    <a href="{{$item['href']}}"><i class="fa {{$item['icon']}}"></i>{{$item['title']}}</a>
                </li>
            @endforeach
        </ul>
    </div>
    <div class="sidebar-footer"></div>
</div>
<!--END MENU SECTION -->
@push('js')
    <script>
        $('.sidebar-toggle').click(function () {
            $("#left").toggleClass("sidebar_hide");
            if ($("#left").hasClass("sidebar_hide")) {
                $("#left").addClass("sidebar-minize");
                $("#left").addClass("sidebar-collapse");
                //setMenuEnable(0);
            } else {
                $("#left").removeClass("sidebar-minize");
                $("#left").removeClass("sidebar-collapse");
                //setMenuEnable(1);
            }
        });
    </script>
@endpush
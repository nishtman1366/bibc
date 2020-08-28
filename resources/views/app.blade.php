<!DOCTYPE html>
<html lang="en">

<!-- BEGIN HEAD-->
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title>تاکسی اینترنتی {{$title}}</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    @stack('meta')
    <link rel="icon" href="../favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="{{assets('vendor/bootstrap/4.3.1/css/bootstrap.min.css')}}">
    <link rel="stylesheet" href="{{assets('vendor/font-awesome-4.6.3/css/font-awesome.min.css')}}"/>
    @stack('css')
</head>
<body class="m-0">
<div class="container-fluid m-0 p-0" style="direction: rtl">
    @yield('main_container')
</div>
<div id="footer">
    <p>&copy; &nbsp;{{@date('Y')}} &nbsp;</p>
</div>
<!--END FOOTER -->
<!-- GLOBAL SCRIPTS -->
<script src="{{assets('vendor/jquery/3.5.1/jquery.min.js')}}"></script>
<script src="{{assets('vendor/popper/1.14.7/popper.min.js')}}"></script>
<script src="{{assets('vendor/bootstrap/4.3.1/js/bootstrap.min.js')}}"></script>
<script src="{{assets('vendor/Axios/axios.min.js')}}"></script>
<script>
    var ajax;
    $(document).ready(function () {
        $('[data-toggle="tooltip"]').tooltip();
        ajax = axios.create({
            baseURL: 'http://localhost/bibc/api/',
            timeout: 5000,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        });
        let companyAccessToken = $('meta[name=company-api-token]').attr("content");
        let driverAccessToken = $('meta[name=driver-api-token]').attr("content");
        let passengerAccessToken = $('meta[name=passenger-api-token]').attr("content");
        if (companyAccessToken !== undefined) {
            ajax.defaults.headers.common['company-api-token'] = companyAccessToken;
        }
        if (driverAccessToken !== undefined) {
            ajax.defaults.headers.common['driver-api-token'] = driverAccessToken;
        }
        if (passengerAccessToken !== undefined) {
            ajax.defaults.headers.common['passenger-api-token'] = passengerAccessToken;
        }
    });
</script>
@stack('js')
</body>
<!-- END BODY-->
</html>
<div id="footer">
    <p>&copy; &nbsp;{{@date('Y')}} &nbsp;</p>
</div>
<!--END FOOTER -->
<!-- GLOBAL SCRIPTS -->
<script src="{{assets('vendor/jquery/3.5.1/jquery.min.js')}}"></script>
<script src="{{assets('vendor/popper/1.14.7/popper.min.js')}}"></script>
<script src="{{assets('vendor/bootstrap/4.3.1/js/bootstrap.min.js')}}"></script>
<script src="{{assets('vendor/modernizr/2.6.2/modernizr.min.js')}}"></script>
<script type="text/javascript" src="{{assets('vendor/morris/raphael-min.js')}}"></script>
<script type="text/javascript" src="{{assets('vendor/morris/morris.min.js')}}"></script>
<script type="text/javascript" src="{{assets('js/actions.js')}}"></script>
<script>
    $(document).ready(function () {
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>
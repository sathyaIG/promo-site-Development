<!-- Footer Start -->
<footer class="footer">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6">
                <script>
                    document.write(new Date().getFullYear())
                </script> &copy; Promo Pricing Tool<a href=""></a>
            </div>

        </div>
    </div>
</footer>
<!-- end Footer -->

</div>
<!-- ============================================================== -->
<!-- End Page content -->
<!-- ============================================================== -->


</div>
<!-- END wrapper -->

@include('admin.partial.right_menu')

<!-- Vendor -->
<script src="{{ asset('public/assets/libs/jquery/jquery.min.js')}}"></script>
<script src="{{ asset('public/assets/libs/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
<script src="{{ asset('public/assets/libs/simplebar/simplebar.min.js')}}"></script>
<script src="{{ asset('public/assets/libs/node-waves/waves.min.js')}}"></script>
<script src="{{ asset('public/assets/libs/waypoints/lib/jquery.waypoints.min.js')}}"></script>
<script src="{{ asset('public/assets/libs/jquery.counterup/jquery.counterup.min.js')}}"></script>
<script src="{{ asset('public/assets/libs/feather-icons/feather.min.js')}}"></script>


<!-- Vendor -->

<script src="{{ asset('public/assets/libs/selectize/js/standalone/selectize.min.js')}}"></script>
<script src="{{ asset('public/assets/libs/mohithg-switchery/switchery.min.js')}}"></script>
<script src="{{ asset('public/assets/libs/multiselect/js/jquery.multi-select.js')}}"></script>
<script src="{{ asset('public/assets/libs/select2/js/select2.min.js')}}"></script>
<script src="{{ asset('public/assets/libs/jquery-mockjax/jquery.mockjax.min.js')}}"></script>
<script src="{{ asset('public/assets/libs/devbridge-autocomplete/jquery.autocomplete.min.js')}}"></script>
<script src="{{ asset('public/assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.js')}}"></script>
<script src="{{ asset('public/assets/libs/bootstrap-maxlength/bootstrap-maxlength.min.js')}}"></script>

<script src="{{ asset('public/assets/js/form-select2.js')}}"></script>

<!-- Init js-->
<!-- <script src="{{ asset('public/assets/js/pages/form-advanced.init.js')}}"></script> -->
<!-- knob plugin -->
<script src="{{ asset('public/assets/libs/jquery-knob/jquery.knob.min.js')}}"></script>

<!-- Vendor -->
<!--Morris Chart-->
<script src="{{ asset('public/assets/libs/morris.js06/morris.min.js')}}"></script>
<script src="{{ asset('public/assets/libs/raphael/raphael.min.js')}}"></script>

<!-- Dashboar init js-->
<script src="{{ asset('public/assets/js/pages/dashboard.init.js')}}"></script>

<!-- App js-->
<script src="{{ asset('public/assets/js/app.min.js')}}"></script>

<!--- Datatable--->
<script src="{{ asset('public/assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('public/assets/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ asset('public/assets/js/table-datatable.js') }}"></script>
@stack('scripts')

<!---Validation --->

<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.2/jquery.validate.min.js"></script> -->
<script src="{{ asset('public/assets/js/jquery.validate.min.js') }}"></script>

<script src="{{ asset('public/assets/js/custon_validation.js') }}"></script>
<!--Validation  -->
<!-- <script src="~/Scripts/jquery-1.7.1.min.js"></script>
<script src="~/Scripts/jquery.validate.js"></script> -->

<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script type="text/javascript">
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $("#condensed-check").on('click', function() {
        $('#condensed-check').hide();
    })
    var toastMixin = Swal.mixin({
        toast: true,
        icon: 'success',
        title: 'General Title',
        animation: true,
        position: 'top-right',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });

    @if($message = Session::get('success'))
    toastMixin.fire({
        icon: 'success',
        animation: true,
        title: '{{ $message }}'
    });
    @endif

    @if($message = Session::get('error'))
    toastMixin.fire({
        icon: 'error',
        animation: true,
        title: '{{ $message }}',
    });
    @endif
</script>

@stack('script')



</body>

<!-- Mirrored from adminlte.io/themes/v3/ by HTTrack Website Copier/3.x [XR&CO'2014], Fri, 19 Mar 2021 05:36:51 GMT -->

</html>
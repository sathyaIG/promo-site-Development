@extends('admin.layouts.admin')
@section('title', 'Dashboard')


@section('content')

<div class="content-page">
  <div class="content">

      <!-- Start Content-->
      <div class="container-fluid">
            
          <!-- start page title -->
          <div class="row">
              <div class="col-12">
                  <div class="page-title-box">                      
                      <h4 class="page-title">Dashboard</h4>
                  </div>
              </div>
          </div>     
          <!-- end page title --> 

          <div class="row">

              <div class="col-xl-3 col-md-6">
                  <div class="card">
                      <div class="card-body">

                          <h4 class="header-title mt-0 mb-4">Pending For  Approval</h4>

                          <div class="widget-chart-1">
                              <div class="widget-detail-1">
                                  <h2 class="fw-normal pt-2 mb-1"> {{ $totalPending }} </h2>
                              </div>
                          </div>
                      </div>
                  </div>
              </div><!-- end col -->

              <div class="col-xl-3 col-md-6">
                <div class="card">
                    <div class="card-body">

                        <h4 class="header-title mt-0 mb-4">Approved Records</h4>

                        <div class="widget-chart-1">
                            <div class="widget-detail-1">
                                <h2 class="fw-normal pt-2 mb-1"> {{ $totalApproved }} </h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- end col -->            
            @if(Auth::user()->role != 5)
            <div class="col-xl-3 col-md-6">
                <div class="card">
                    <div class="card-body">

                        <h4 class="header-title mt-0 mb-4">No. Of Manufacturers</h4>
                        <div class="widget-chart-1">
                            <div class="widget-detail-1">
                                <h2 class="fw-normal pt-2 mb-1" >
                                <?php 
                                    if(Auth::user()->manufacturerLists == null && Auth::user()->role != 1) {
                                        $manufacturer = '0';
                                    }else{
                                        $manufacturer = $manufacturerLists;
                                    }
                                    ?>
                                {{$manufacturer}}</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- end col -->
            @endif
            <div class="col-xl-3 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h4 class="header-title mt-0 mb-4">Rejected Records</h4>
                        <div class="widget-chart-1">
                            <div class="widget-detail-1">
                                <h2 class="fw-normal pt-2 mb-1"> {{$totalReject}} </h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- end col -->

            
          </div>
          
          
      </div> <!-- container -->

  </div> <!-- content -->

  <!-- Footer Start -->
  <footer class="footer">
      <div class="container-fluid">
          <div class="row">
              <div class="col-md-6">
                  <script>document.write(new Date().getFullYear())</script>2023 © Adminto theme by <a href="">Coderthemes</a> 
              </div>
              <div class="col-md-6">
                  <div class="text-md-end footer-links d-none d-sm-block">
                      <a href="javascript:void(0);">About Us</a>
                      <a href="javascript:void(0);">Help</a>
                      <a href="javascript:void(0);">Contact Us</a>
                  </div>
              </div>
          </div>
      </div>
  </footer>
  <!-- end Footer -->

</div><!-- content -->

  <script>
    // setTimeout(function () {
    //     $('.close_session').hide();
    //   }, 3000);
  </script>

  @stop
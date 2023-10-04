  <!-- Topbar Start -->
  <div class="navbar-custom" style="background-color: #fff!important;">
    <div class="container-fluid">
         <ul class="list-unstyled topnav-menu float-end mb-0">

            <li class="dropdown d-inline-block d-lg-none">
                <a class="nav-link dropdown-toggle arrow-none waves-effect waves-light" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                    <i class="fe-search noti-icon"></i>
                </a>
                <div class="dropdown-menu dropdown-lg dropdown-menu-end p-0">
                    <form class="p-3">
                        <input type="text" class="form-control" placeholder="Search ..." aria-label="Recipient's username">
                    </form>
                </div>
            </li>

            <li class="dropdown notification-list topbar-dropdown">
                <a class="nav-link dropdown-toggle nav-user me-0 waves-effect waves-light" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                    <img src="{{ getProfileImage(Auth::user()->profile_image) }}" alt="user-image" class="rounded-circle">
                    <span class="pro-user-name ms-1" style="color: black !important;">
                    {{ Auth::user()->name }} <i class="mdi mdi-chevron-down"></i> 
                    </span>
                </a>
                <div class="dropdown-menu dropdown-menu-end profile-dropdown ">
                    <!-- item-->
                    <!-- <div class="dropdown-header noti-title">
                        <h6 class="text-overflow m-0">Welcome !</h6>
                    </div> -->

                    <!-- item-->
                    <!-- <a href="contacts-profile.html" class="dropdown-item notify-item">
                        <i class="fe-user"></i>
                        <span>My Account</span>
                    </a> -->

                    <!-- item-->
                    <!-- <a href="auth-lock-screen.html" class="dropdown-item notify-item">
                        <i class="fe-lock"></i>
                        <span>Lock Screen</span>
                    </a> -->

                    <!-- <div class="dropdown-divider"></div> -->

                    <!-- item-->
                    <a href="{{ admin_url('logout') }}" class="dropdown-item notify-item">
                        <i class="fe-log-out"></i>
                        <span>Logout</span>
                    </a>

                </div>
            </li>

        </ul>

        <!-- LOGO -->
        <div class="logo-box">
            <div class="row">
    
                <div class="col">
                    <a href="" onClick="window.location.reload();" class="logo logo-light text-center">
                        <span class="logo-sm">
                            <img src="{{ asset('public/assets/images/login-logo-mini.png')}}" alt="" height="22">
                        </span>
                        <span class="logo-lg">
                            <img src="{{ asset('public/assets/images/logo.png')}}" alt=""width="140" height="56">
                        </span>
                    </a>
                    <a href="" onClick="window.location.reload();" class="logo logo-dark text-center">
                        <span class="logo-sm">
                            <img src="{{ asset('public/assets/images/login-logo-mini.png')}}" alt="" height="22">
                        </span>
                        <span class="logo-lg">
                            <img src="{{ asset('public/assets/images/logo.png')}}" alt=""  width="140" height="56">
                        </span>
                    </a>
    
                </div>
                <!-- <div class="col">
                    <div class="form-check form-switch mb-2">
                        <input type="checkbox" style="margin: auto;margin-top: 28px;width: 30px;" class="form-check-input" name="leftbar-size" value="condensed" id="condensed-check" checked />
                    </div>
                    <icon class=" fas fa-list-ul" id="slide-change"></icon>
                </div> -->
    
    
            </div>
    
    
    
    
            <!-- <input type="checkbox" class="form-check-input" name="leftbar-size" value="condensed" id="condensed-check" /> -->
    
        </div>

        <ul class="list-unstyled topnav-menu topnav-menu-left mb-0">

            <li>
                <!-- Mobile menu toggle (Horizontal Layout)-->
                <a class="navbar-toggle nav-link" data-bs-toggle="collapse" data-bs-target="#topnav-menu-content">
                    <div class="lines">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </a>
                <!-- End mobile menu toggle-->
            </li>
 
        </ul>

        <div class="clearfix"></div> 

    </div>
  
</div>
<!-- end Topbar -->
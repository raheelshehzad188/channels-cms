<!-- BEGIN: Main Menu-->

    <div class="main-menu menu-fixed menu-light menu-accordion menu-shadow" data-scroll-to-active="true">

        <div class="navbar-header">

            <ul class="nav navbar-nav flex-row">

                <li class="nav-item mr-auto"><a class="navbar-brand" href="../../../html/ltr/vertical-menu-template/index.html">

                    <?php

                $user = $this->session->userdata('knet_login');
                $logo = 'https://tadhamn.org/wp-content/uploads/2022/03/1.png';

        ?>

        <img src="<?= $logo; ?>" style="width: 78%;margin: 13px auto;" />

                    </a></li>

            </ul>

        </div>

        <div class="shadow-bottom"></div> 

        <div class="main-menu-content">

        

            <ul class=" navigation navigation-main" id="main-menu-navigation" data-menu="menu-navigation">

                <li><a href="<?= base_url('admin/user/all');?>"><i class="feather icon-circle"></i><span class="menu-item" data-i18n="Details">Manage Users</span></a>
                <li><a href="<?= base_url('admin/worthiest/all');?>"><i class="feather icon-circle"></i><span class="menu-item" data-i18n="Details">Manage Worthiests</span></a>
                        <li><a href="<?= base_url('admin/order/all');?>"><i class="feather icon-circle"></i><span class="menu-item" data-i18n="Details">Mansge Orders</span></a>
                        <li><a href="<?= base_url('admin/organization/all');?>"><i class="feather icon-circle"></i><span class="menu-item" data-i18n="Details">Mansge Organizations</span></a>

                        </li>

                

            </ul>

        </div>

    </div>
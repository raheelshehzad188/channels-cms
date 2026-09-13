        <nav class="navbar-default navbar-static-side" role="navigation">
            <div class="sidebar-collapse">
                <ul class="nav metismenu" id="side-menu">
                    <li class="nav-header">
                <div class="dropdown profile-element">
                    <span>
                            <img alt="image" class="img-circle" src="<?= $assets ?>img/profile_small.jpg" />
                             </span>
                            <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <span class="clear">
                            <span class="block m-t-xs">
                                <strong class="font-bold"><?= htmlspecialchars(ec_display_name()) ?></strong>
                            </span>
                            <span class="text-muted text-xs block">
                                <?= htmlspecialchars(ec_role_label(ec_user()->roleID)) ?> <b class="caret"></b>
                            </span>
                        </span>
                    </a>
                            <ul class="dropdown-menu animated fadeInRight m-t-xs">
                        <li><a href="<?= base_url('admin/admin/logout') ?>">Logout</a></li>
                            </ul>
                        </div>
                <div class="logo-element">EC</div>
                                    </li>
                                    
            <?php if (ec_is_admin()): ?>
            <li class="<?= $this->uri->segment(2) == 'admin' ? 'active' : '' ?>">
                <a href="<?= base_url('admin/admin') ?>"><i class="fa fa-th-large"></i> <span class="nav-label">Dashboard</span></a>
                            </li>
            <li class="<?= $this->uri->segment(2) == 'countries' ? 'active' : '' ?>">
                <a href="<?= base_url('admin/countries') ?>"><i class="fa fa-globe"></i> <span class="nav-label">Countries</span></a>
                                    </li>
            <li class="<?= $this->uri->segment(2) == 'categories' ? 'active' : '' ?>">
                <a href="<?= base_url('admin/categories') ?>"><i class="fa fa-tags"></i> <span class="nav-label">Categories</span></a>
                                    </li>
            <li class="<?= $this->uri->segment(2) == 'suppliers' ? 'active' : '' ?>">
                <a href="<?= base_url('admin/suppliers') ?>"><i class="fa fa-truck"></i> <span class="nav-label">Suppliers</span></a>
                                    </li>
            <li class="<?= $this->uri->segment(2) == 'users' ? 'active' : '' ?>">
                <a href="<?= base_url('admin/users') ?>"><i class="fa fa-users"></i> <span class="nav-label">Users</span></a>
                                    </li>
            <li class="<?= $this->uri->segment(2) == 'themes' ? 'active' : '' ?>">
                <a href="<?= base_url('admin/themes') ?>"><i class="fa fa-paint-brush"></i> <span class="nav-label">Themes</span></a>
                                    </li>
            <li class="<?= $this->uri->segment(2) == 'stores' ? 'active' : '' ?>">
                <a href="<?= base_url('admin/stores') ?>"><i class="fa fa-shopping-bag"></i> <span class="nav-label">Stores</span></a>
                                    </li>
            <li class="<?= $this->uri->segment(2) == 'pricing' ? 'active' : '' ?>">
                <a href="<?= base_url('admin/pricing') ?>"><i class="fa fa-money"></i> <span class="nav-label">Pricing</span></a>
                            </li>
            <li class="<?= $this->uri->segment(2) == 'smtp' ? 'active' : '' ?>">
                <a href="<?= base_url('admin/smtp') ?>"><i class="fa fa-envelope"></i> <span class="nav-label">SMTP</span></a>
            </li>
            <li class="<?= $this->uri->segment(2) == 'paypal' ? 'active' : '' ?>">
                <a href="<?= base_url('admin/paypal') ?>"><i class="fa fa-credit-card"></i> <span class="nav-label">Gateway</span></a>
            </li>
            <?php endif; ?>

            <li class="<?= $this->uri->segment(2) == 'orders' ? 'active' : '' ?>">
                <a href="<?= base_url('admin/orders') ?>"><i class="fa fa-shopping-cart"></i> <span class="nav-label">Orders</span></a>
                                    </li>
            <li class="<?= $this->uri->segment(2) == 'products' ? 'active' : '' ?>">
                <a href="<?= base_url('admin/products') ?>"><i class="fa fa-cubes"></i> <span class="nav-label">Products</span></a>
                                    </li>
            <?php if (ec_is_admin()): ?>
            <li class="<?= $this->uri->segment(3) == 'unknown' ? 'active' : '' ?>">
                <a href="<?= base_url('admin/products/unknown') ?>"><i class="fa fa-question-circle"></i> <span class="nav-label">Unknown Imports</span></a>
            </li>
            <li class="<?= $this->uri->segment(2) == 'flush-data' ? 'active' : '' ?>">
                <a href="<?= base_url('admin/flush-data') ?>"><i class="fa fa-trash"></i> <span class="nav-label">Flush Data</span></a>
            </li>
            <?php endif; ?> 
                                </ul>
                                </div>
        </nav>

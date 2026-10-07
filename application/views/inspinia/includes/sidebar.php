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
            <li class="<?= $this->uri->segment(2) == 'categories' && $this->uri->segment(3) != 'json' ? 'active' : '' ?>">
                <a href="<?= base_url('admin/categories') ?>"><i class="fa fa-tags"></i> <span class="nav-label">Categories</span></a>
                                    </li>
            <li class="<?= $this->uri->segment(3) == 'json' ? 'active' : '' ?>">
                <a href="<?= base_url('admin/categories/json') ?>"><i class="fa fa-code"></i> <span class="nav-label">Category JSON</span></a>
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
            <li class="<?= $this->uri->segment(2) == 'offers' ? 'active' : '' ?>">
                <a href="<?= base_url('admin/offers') ?>"><i class="fa fa-tag"></i> <span class="nav-label">Offers</span></a>
            </li>
            <li class="<?= $this->uri->segment(2) == 'smtp' ? 'active' : '' ?>">
                <a href="<?= base_url('admin/smtp') ?>"><i class="fa fa-envelope"></i> <span class="nav-label">SMTP</span></a>
            </li>
            <li class="<?= $this->uri->segment(2) == 'ai-settings' ? 'active' : '' ?>">
                <a href="<?= base_url('admin/ai-settings') ?>"><i class="fa fa-key"></i> <span class="nav-label">AI Settings</span></a>
            </li>
            <li class="<?= $this->uri->segment(2) == 'whatsapp' ? 'active' : '' ?>">
                <a href="<?= base_url('admin/whatsapp') ?>"><i class="fa fa-whatsapp"></i> <span class="nav-label">WhatsApp</span></a>
            </li>
            <li class="<?= $this->uri->segment(2) == 'paypal' ? 'active' : '' ?>">
                <a href="<?= base_url('admin/paypal') ?>"><i class="fa fa-credit-card"></i> <span class="nav-label">Gateway</span></a>
            </li>
            <li class="<?= $this->uri->segment(2) == 'social' ? 'active' : '' ?>">
                <a href="<?= base_url('admin/social') ?>"><i class="fa fa-share-alt"></i> <span class="nav-label">Social</span></a>
            </li>
            <?php endif; ?>

            <li class="<?= $this->uri->segment(2) == 'accounting' ? 'active' : '' ?>">
                <a href="<?= base_url('admin/accounting') ?>"><i class="fa fa-calculator"></i> <span class="nav-label">Accounting</span></a>
                                    </li>
            <li class="<?= $this->uri->segment(2) == 'orders' ? 'active' : '' ?>">
                <a href="<?= base_url('admin/orders') ?>"><i class="fa fa-shopping-cart"></i> <span class="nav-label">Orders</span></a>
                                    </li>
            <?php if (ec_is_admin()): ?>
            <li class="<?= $this->uri->segment(2) == 'listings' ? 'active' : '' ?>">
                <a href="<?= base_url('admin/listings') ?>"><i class="fa fa-th-list"></i> <span class="nav-label">Store Listings</span></a>
            </li>
            <?php endif; ?>
            <li class="<?= $this->uri->segment(2) == 'products' && $this->uri->segment(3) != 'unknown' ? 'active' : '' ?>">
                <a href="<?= base_url('admin/products') ?>"><i class="fa fa-cubes"></i> <span class="nav-label">Products</span></a>
                                    </li>
            <?php if (ec_is_admin()): ?>
            <li class="<?= $this->uri->segment(2) == 'ai-content' ? 'active' : '' ?>">
                <a href="<?= base_url('admin/ai-content') ?>"><i class="fa fa-magic"></i> <span class="nav-label">AI Content</span></a>
            </li>
            <?php endif; ?>
            <li class="<?= $this->uri->segment(2) == 'product-analyzer' ? 'active' : '' ?>">
                <a href="<?= base_url('admin/product-analyzer') ?>"><i class="fa fa-line-chart"></i> <span class="nav-label">Product Analyzer</span></a>
                                    </li>
            <li class="<?= $this->uri->segment(2) == 'store-profitability' ? 'active' : '' ?>">
                <a href="<?= base_url('admin/store-profitability') ?>"><i class="fa fa-pie-chart"></i> <span class="nav-label">Store Profitability</span></a>
                                    </li>
            <li class="<?= $this->uri->segment(2) == 'store-analytics' ? 'active' : '' ?>">
                <a href="<?= base_url('admin/store-analytics') ?>"><i class="fa fa-eye"></i> <span class="nav-label">Live Store Analytics</span></a>
                                    </li>
            <?php if (ec_is_admin()): ?>
            <li class="<?= $this->uri->segment(2) == 'product-analytics' ? 'active' : '' ?>">
                <a href="<?= base_url('admin/product-analytics') ?>"><i class="fa fa-bar-chart"></i> <span class="nav-label">Product Analytics</span></a>
                                    </li>
            <?php endif; ?>
            <li class="<?= $this->uri->segment(2) == 'hunting' ? 'active' : '' ?>">
                <a href="<?= base_url('admin/hunting') ?>"><i class="fa fa-search"></i> <span class="nav-label">Product Hunting</span></a>
                                    </li>
            <?php if (ec_is_admin()): ?>
            <li class="<?= $this->uri->segment(3) == 'unknown' ? 'active' : '' ?>">
                <a href="<?= base_url('admin/products/unknown') ?>"><i class="fa fa-question-circle"></i> <span class="nav-label">Failed Imports</span></a>
            </li>
            <li class="<?= $this->uri->segment(2) == 'flush-data' ? 'active' : '' ?>">
                <a href="<?= base_url('admin/flush-data') ?>"><i class="fa fa-trash"></i> <span class="nav-label">Flush Data</span></a>
            </li>
            <?php endif; ?> 
                                </ul>
                                </div>
        </nav>

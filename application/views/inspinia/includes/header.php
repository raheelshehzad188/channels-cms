<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?= isset($title) ? htmlspecialchars($title) . ' | ' : '' ?><?= htmlspecialchars(config_item('app_name')) ?></title>
    <link href="<?= $assets ?>css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= $assets ?>font-awesome/css/font-awesome.css" rel="stylesheet">
    <link href="<?= $assets ?>css/plugins/toastr/toastr.min.css" rel="stylesheet">
    <link href="<?= $assets ?>css/animate.css" rel="stylesheet">
    <link href="<?= $assets ?>css/style.css" rel="stylesheet">
    <script src="<?= $assets ?>js/jquery-3.1.1.min.js"></script>
</head>
<body>
<div id="wrapper">
<?php include "sidebar.php"; ?>
    <div id="page-wrapper" class="gray-bg">
        <div class="row border-bottom">
            <nav class="navbar navbar-static-top" role="navigation" style="margin-bottom: 0">
                <div class="navbar-header">
                    <a class="navbar-minimalize minimalize-styl-2 btn btn-primary" href="#"><i class="fa fa-bars"></i></a>
                </div>
                <ul class="nav navbar-top-links navbar-right">
                    <li>
                        <span class="m-r-sm text-muted welcome-message">
                            Welcome, <?= htmlspecialchars(ec_display_name()) ?>
                            (<?= htmlspecialchars(ec_role_label(ec_user()->roleID)) ?>)
                        </span>
                    </li>
                    <li>
                        <a href="<?= base_url('admin/admin/logout') ?>">
                            <i class="fa fa-sign-out"></i> Log out
                        </a>
                    </li>
                </ul>
            </nav>
        </div>

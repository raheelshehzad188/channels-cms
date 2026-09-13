<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(config_item('app_name')) ?> | Login</title>
    <link href="<?= $assets ?>css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= $assets ?>font-awesome/css/font-awesome.css" rel="stylesheet">
    <link href="<?= $assets ?>css/animate.css" rel="stylesheet">
    <link href="<?= $assets ?>css/style.css" rel="stylesheet">
</head>
<body class="gray-bg">
    <div class="loginColumns animated fadeInDown">
        <div class="row">
            <div class="col-md-6">
                <h2 class="font-bold">Welcome to <?= htmlspecialchars(config_item('app_name')) ?></h2>
                <p>Sign in as Super Admin to manage countries, suppliers, users and products.</p>
                <p>Ecommerce users can sign in to list, edit and view product details.</p>
            </div>
            <div class="col-md-6">
                <div class="ibox-content">
                    <form class="m-t" method="post" role="form" action="<?= base_url('login/post') ?>">
                        <?php $this->load->view('flash') ?>
                        <div class="form-group">
                            <input type="text" class="form-control" name="uname" placeholder="Username or Email" required>
                        </div>
                        <div class="form-group">
                            <input type="password" class="form-control" placeholder="Password" name="upass" required>
                        </div>
                        <button type="submit" class="btn btn-primary block full-width m-b">Login</button>
                    </form>
                    <p class="m-t">
                        <small>Super Admin: <strong>admin / admin</strong><br>Ecommerce: <strong>ecommerce / ecommerce</strong></small>
                    </p>
                </div>
            </div>
        </div>
        <hr/>
        <div class="row">
            <div class="col-md-6"><?= htmlspecialchars(config_item('app_name')) ?></div>
            <div class="col-md-6 text-right"><small>&copy; <?= date('Y') ?></small></div>
        </div>
    </div>
</body>
</html>

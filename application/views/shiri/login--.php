<!doctype html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Shireen al Mutawa</title>
	<link rel="stylesheet" type="text/css" href="<?= $assets ?>css/font-awesome.min.css">
	<link rel="stylesheet" type="text/css" href="<?= $assets ?>css/bootstrap.min.css">
	<link rel="stylesheet" href="<?= $assets ?>css/owl.carousel.min.css">
	<link rel="stylesheet" type="text/css" href="<?= $assets ?>css/owl.theme.default.min.css">
	<link rel="stylesheet" type="text/css" href="<?= $assets ?>css/style.css">
</head>
<body>
<div id="login">
	<div class="row">
		<div class="col-md-6 col-sm-6 col-xs-6">
			<img src="<?= $assets ?>images/pic69.png">
		</div>
		<div class="col-md-6 col-sm-6 col-xs-6">
			<div class="logo">
				<a href="index.html"><img src="<?= $assets ?>images/logo.png"></a>
			</div>
			<div class="loginForm">
				<form action="/action_page.php" style="max-width:500px;margin:auto">
				  	<h2>MEMBER LOGIN</h2>
					<div class="input-container">
					    <i class="fa fa-user icon"></i>
					    <input class="input-field" type="text" placeholder="" name="usrnm">
					</div>
					<div class="input-container">
					    <i class="fa fa-lock icon"></i>
					    <input class="input-field" type="password" placeholder="" name="psw">
					</div>
					<input type="submit" class="btn" value="LOG IN">
					<a href="#">Forgot Password</a>
					<div class="noAccount">
						<a href="#">Dont’t have an account?</a>
					</div>
				</form>
			</div>
		</div>
	</div>	
</div>
<script type="text/javascript" js="<?= $assets ?>js/jquery.js"></script>
<script type="text/javascript" js="<?= $assets ?>js/bootstrap.min.js"></script>
<script type="text/javascript" js="<?= $assets ?>js/owl.carousel.min.js"></script>


</body>
</html>
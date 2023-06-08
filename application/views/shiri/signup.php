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
<div id="login" >
	<div class="row">
		<div class="col-md-6 col-sm-6 col-xs-6">
			<img src="<?= $assets ?>images/pic70.png">
		</div>
		<div class="col-md-6 col-sm-6 col-xs-6">
			<div class="logo">
				<a href="index.html"><img src="<?= $assets ?>images/logo.png"></a>
			</div>
			<div class="loginForm signUp">
				<form action="<?= base_url('/auth/create/'); ?>" method="post" style="max-width:500px;margin:auto">
				  	<h2>CREATE YOUR ACCOUNT</h2>
				  	<?php
				  	    $this->load->view('flash');
				  	?>
					<div class="input-container">
						<label>Your Email Address <strong>*</strong></label>
					    <img src="<?= $assets ?>images/pic71.png">
					    <input class="input-field" type="email" placeholder="" name="email">
					    <a href="#">Already have an account?</a>
					</div>
					<div class="left">
						<div class="input-container ">
							<label>Set Password <strong>*</strong></label>
						    <img src="<?= $assets ?>images/pic72.png">
						    <input class="input-field" type="password" placeholder="" name="upass">
						</div>
					</div>
					<div class="right">
						<div class="input-container ">
							<label>Confirm Password </label>
						    <input class="input-field" type="password" placeholder="" name="cpass">
						</div>
					</div>
					<input type="submit" class="btn" value="Sign Up!">
					
					
				</form>
			</div>
		</div>
	</div>	
</div>
<script type="text/javascript" src="<?= $assets ?>js/jquery.js"></script>
<script type="text/javascript" src="<?= $assets ?>js/bootstrap.min.js"></script>
<script type="text/javascript" src="<?= $assets ?>js/owl.carousel.min.js"></script>


</body>
</html>
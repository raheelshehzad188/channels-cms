<!doctype html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Shireen al Mutawa</title>
	<link rel="stylesheet" type="text/css" href="<?= $assets ?>/css/font-awesome.min.css">
	<link rel="stylesheet" type="text/css" href="<?= $assets ?>/css/bootstrap.min.css">
	<link rel="stylesheet" href="<?= $assets ?>/css/owl.carousel.min.css">
	<link rel="stylesheet" type="text/css" href="<?= $assets ?>/css/owl.theme.default.min.css">
	<link rel="stylesheet" type="text/css" href="<?= $assets ?>/css/style.css">
</head>
<body>
<div id="wrapper">
	<header id="header" >
		<div class="">
			<div class="logo">
				<a href="index.html"><img src="<?= $assets ?>images/logo.png"></a>
			</div>
			<button type="button" class="navbar-toggle open" data-toggle="modal" data-target="#myModal"><i class="fa fa-bars"></i></button>
		</div>
		<div class="modal sidebars" id="myModal">
			<div class="sidebarContents">
				<div id="">
					<ul class="languages">
						<li><a href="#">AR</a></li>
						<li class="active"><a href="#">EN</a></li>
					</ul>
					<button type="button" class="navbar-toggle close" data-toggle="modal" data-target="#myModal"><i class="fa fa-times"></i></button>
				</div>
				<?php
				$this->load->view('includes/menu');
				?>
			</div>	
		</div>
	</header>
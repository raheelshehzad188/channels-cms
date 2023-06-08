<?php
function addOrdinalNumberSuffix($num) {
    if (!in_array(($num % 100),array(11,12,13))){
      switch ($num % 10) {
        // Handle 1st, 2nd, 3rd
        case 1:  return $num.'st';
        case 2:  return $num.'nd';
        case 3:  return $num.'rd';
      }
    }
    return $num.'th';
  }
?>
	<div class="maincontents">
		<section id="banner">
			<img src="<?= $assets ?>images/pic64.png">
			<div class="banText">
				<?php
				$img = '';
				if($status)
				{
					$img = $assets.'images/pic67.png';
					?>
					<h2>ORDER SUCCESSFUL</h2>
					<?php

				} else{ 
					$img = $assets.'images/pic68.png'; ?> 
					<h2>ORDER UNSUCCESSFUL</h2> <?php } 
				$date = addOrdinalNumberSuffix(date('d')).' '.date('F').' '.date('Y'); ?> </div>
				</section> <div class="aboutUs"> <div class="container">
				<div class="row"> <div class="congradulations"> <img
				src="<?= $img ?>"> <h3>THANKS FOR YOUR PURCHASE!</h3>
				<strong><span> ORDER NUMBER:</span> <?= $order
				['id'] ?></strong> <strong><span>DATE:</span> <?=
				$date ?></strong> <strong><span>TOTAL:</span> <?= $order
				['total'] ?>
				KD</strong> <strong><span>PAYMENT METHOD:</span>
				KNET</strong> </div> </div> </div> </div>
		
	</div>
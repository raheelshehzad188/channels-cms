
	<div class="maincontents cart">
		<section id="banner">
			<img src="<?= $assets ?>images/pic64.png">
			<div class="banText">
				<h2>CHECKOUT</h2>
				
			</div>
		</section>
		<div class="row">
			<div class="col-md-7 col-sm-7">
				<div class="cart-items">
					    <?php
					    $modal->table = 'courses';
					    $modal->key = 'id';
					    $cart = array();
                	    if(isset($_SESSION['cart']))
                	    { 
                	        $cart = $_SESSION['cart'];
                	    } 
                	    $tot = 0;
                	    foreach($cart as $k=> $v)
                	    {
                	        $sing = $modal->getbyid($v['pid']);
                	        $tot = $tot + $sing->price;
                	        $banner = $modal->getimg($sing->mediaID);
                	        ?>
                	        
					<div class="cartItem">
						<div class="item"><img src="<?= $banner ?>"></div>
						<div class="itemDetail">
							<strong><?= $sing->name ?></strong> 
							<p><?= $sing->detail; ?></p>
						</div>
						<div class="incDec">
							<span><?= $sing->price ?> KD</span>
							
                            <a href="#"><i class="fa fa-times"></i></a>
						</div>
					</div>
                	        <?php
                	    }
					    ?>
					
				</div>
			</div>
			<div class="col-md-5 col-sm-5">
				<div class="cartPayment">
					<div class="subTotal">
						<strong>SubTotal
						<span><?= $tot; ?> KD</span></strong>
					</div>
					<div class="subTotal d-none hidden" style="display:none;">
						<strong>DISCOUNT
						<span style="color: #fff;">- 2.000 KD</span></strong>
					</div>
					<div class="subTotal">
						<strong>TOTAL
						<span><?= $tot; ?> KD</span></strong>
					</div>
					<form action="<?= base_url('api/checkout') ?>" id="checkout_form" method="post" >
					  	<div class="proCode">
					  		<label>Enter promotional code</label>
						    <input class="input-field" type="text" placeholder=" " name="">
						</div>
						<div class="proCode">
					  		<label>Address</label>
						    <input class="input-field" type="text" placeholder="Mangaf" name="">
						</div>
						<ul class="paymentMethod">
							<li class="on"><a href="#"><img src="<?= $assets ?>images/pic51.png"></a></li>
							<li class="off"><a href="#"><img src="<?= $assets ?>images/pic52.png"></a></li>
						</ul>

						<div class="input-container">
						    <input class="input-field" type="text" placeholder="Name" name="uname">
						</div>
						<div class="input-container">
						    <input class="input-field" type="text" placeholder="Email" name="email">
						</div>
						<div class="input-container">
						    <input class="input-field" type="text" placeholder="Phone" name="phone">
						</div>
						<a onclick="submit_form('checkout_form')" type="submit" class="btn" value="">Checkout <img src="<?= $assets ?>images/pic53.png"></a>
					</form>
				</div>
			</div>
		</div>
		
	</div>

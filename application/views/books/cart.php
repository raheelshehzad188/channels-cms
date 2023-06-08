<div class="maincontents cart">
		<div class="row">
			<div class="col-md-7 col-sm-7">
				<div class="cart-items">
					<h1>CART</h1>
					<div class="cartItem">
						<div class="item"><img src="<?= $assets; ?>images/pic50.png"></div>
						<div class="itemDetail">
							<strong>Grilled Chicken Mushroom</strong> 
							<p>Served with 2 side dishes: rice, French fries, mashed potatoes, corn, sautéed vegetables, spinach, oven potatoes, croquette potatoes or gratin potatoes or one pasta: spaghetti, penne or fettuccini</p>
						</div>
						<div class="incDec">
							<span>10.2 KD</span>
							<div class="input-group">
                                <span class="input-group-btn">
                                    <button type="button" class="quantity-right-plus btn btn-success btn-number" data-type="plus" data-field="">
                                        <span class="glyphicon glyphicon-plus"></span>
                                    </button>
                                </span>
                                <input type="text" id="quantity" name="quantity" class="form-control input-number" value="01" min="1" max="100">
                                <span class="input-group-btn">
                                    <button type="button" class="quantity-left-minus btn btn-danger btn-number"  data-type="minus" data-field="">
                                      <span class="glyphicon glyphicon-minus"></span>
                                    </button>
                                </span>
                            </div>
                            <a href="#"><i class="fa fa-times"></i></a>
						</div>
					</div>
					<div class="cartItem">
						<div class="item"><img src="<?= $assets; ?>images/pic50.png"></div>
						<div class="itemDetail">
							<strong>Rib-Eye and Salmon Steak</strong> 
							<p>Served with Mexican rice and one side dish</p>
						</div>
						<div class="incDec">
							<span>50.4 KD</span>
							<div class="input-group">
                                <span class="input-group-btn">
                                    <button type="button" class="quantity-right-plus btn btn-success btn-number" data-type="plus" data-field="">
                                        <span class="glyphicon glyphicon-plus"></span>
                                    </button>
                                </span>
                                <input type="text" id="quantity" name="quantity" class="form-control input-number" value="03" min="1" max="100">
                                <span class="input-group-btn">
                                    <button type="button" class="quantity-left-minus btn btn-danger btn-number"  data-type="minus" data-field="">
                                      <span class="glyphicon glyphicon-minus"></span>
                                    </button>
                                </span>
                            </div>
                            <a href="#"><i class="fa fa-times"></i></a>
						</div>
					</div>
					<div class="subTotal">
						<strong>SubTotal
						<span>60.6 KD</span></strong>
					</div>
				</div>
			</div>
			<div class="col-md-5 col-sm-5">
				<div class="cartPayment">
					<strong>Payment </strong>
					<form action="" >
					  	<div class="radio">
						  	<label><input type="radio" name="optradio">Online Payment</label>
						</div>
						<ul class="paymentMethod">
							<li class="on"><a href="#"><img src="<?= $assets; ?>images/pic51.png"></a></li>
							<li class="off"><a href="#"><img src="<?= $assets; ?>images/pic52.png"></a></li>
						</ul>

						<div class="input-container">
						    <input class="input-field" type="text" placeholder="Cardholder Name" name="email">
						</div>
						<div class="input-container">
						    <input class="input-field" type="text" placeholder="Card Number" name="">
						</div>
						<div class="input-container exp">
						    <input class="input-field" type="text" placeholder="Exp. Date" name="">
						    <input class="input-field" type="text" placeholder="CVV" name="">
						</div>
						<a href="#" type="submit" class="btn" value="">Checkout <img src="<?= $assets; ?>images/pic53.png"></a>
					</form>
				</div>
			</div>
		</div>
	</div>

	<div class="maincontents module">
		<section id="banner">
			<img src="<?= $assets ?>images/pic66.png">
			<div class="banText">
				<h2>Categories</h2>
				<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Quis ipsum suspendisse ultrices gravida. Risus commodo viverra maecenas accumsan lacus vel facilisis.  </p>
			</div>
		</section>
		<div class="container">
			<div class="category_page">
				<section id="courses">
					<h2>CHOOSE YOUR CATERGORY</h2>
					<div class="owl-carousel owl-theme">
			            <div class="item">
			              	<img src="<?= $assets ?>images/cat1.png">
			            </div>
			            <div class="item">
			              	<img src="<?= $assets ?>images/cat2.png">
			            </div>
			            <div class="item">
			              	<img src="<?= $assets ?>images/cat3.png">
			            </div>
			            <div class="item">
			              	<img src="<?= $assets ?>images/cat1.png">
			            </div>
			            <div class="item">
			              	<img src="<?= $assets ?>images/cat2.png">
			            </div>
			            <div class="item">
			              	<img src="<?= $assets ?>images/cat3.png">
			            </div>
			            <div class="item">
			              	<img src="<?= $assets ?>images/cat1.png">
			            </div>
			            <div class="item">
			              	<img src="<?= $assets ?>images/cat2.png">
			            </div>
			            <div class="item">
			              	<img src="<?= $assets ?>images/cat3.png">
			            </div>
			        </div>
				</section>
				<p>First and foremost welcome to The Knowledge Business Blueprint. On behalf of Tony & Dean, we wanted to welcome you to the absolute GOLD standard of education when it comes to creating an impactful and profitable small group, workshop or mastermind. To get you started on the right foot, please follow the "First 5 Steps" we recommend all new students do when they log in for the first time...</p>
			</div> 
		</div>
			
		
	</div>
	<script>
    $(document).ready(function() {
      $('.owl-carousel').owlCarousel({
        loop: true,
        responsiveClass: true,
        margin: 30,
        responsive: {
          0: {
            items: 1,
            nav: true
          },
          600: {
            items: 1,
            nav: false
          },
          1000: {
            items: 3,
            nav: true,
            loop: false
          }
        }
      })
    })

   
  </script>
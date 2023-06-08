
	<div class="maincontents">
		<div class="stickySlider">
			<div class="SliderHolder">
				<div class="stickyBg">
				</div>
				<div id="myCarousel" class="carousel slide" data-ride="carousel">
				    <!-- Indicators -->
				    <ol class="carousel-indicators">
				      <li data-target="#myCarousel" data-slide-to="0" class="active"></li>
				      <li data-target="#myCarousel" data-slide-to="1"></li>
				      <li data-target="#myCarousel" data-slide-to="2"></li>
				      <li data-target="#myCarousel" data-slide-to="3"></li>
				    </ol>

				    <!-- Wrapper for slides -->
				    <div class="carousel-inner">
				      <div class="item active">
				        <img src="<?= $assets ?>images/pic2.png">
				      </div>

				      <div class="item">
				        <img src="<?= $assets ?>images/pic2.png">
				      </div>

				      <div class="item">
				        <img src="<?= $assets ?>images/pic2.png">
				      </div>

				      <div class="item">
				        <img src="<?= $assets ?>images/pic2.png">
				      </div>
				    </div>

				    <!-- Left and right controls -->
				    <a class="left carousel-control" href="#myCarousel" data-slide="prev">
				      <img src="<?= $assets ?>images/prev.png">
				      <span class="sr-only">Previous</span>
				    </a>
				    <a class="right carousel-control" href="#myCarousel" data-slide="next">
				      <img src="<?= $assets ?>images/next.png">
				      <span class="sr-only">Next</span>
				    </a>
				</div>
			</div>
		</div>
		<div class="container"> 
			<section id="courses">
				<div class="courseLogo"><img src="<?= $assets ?>images/pic54.png"></div>
				<div class="category_page">
					<section id="courses">
						<h2 style="color: #fff !important;">CHOOSE YOUR CATERGORY</h2>
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
				<h2>Select a course to continue</h2>
				<div class="owl-carousel owl-theme">
		            <?php
		            $modal->table = 'courses';
		            $modal->key = 'id';
		            $cour = $modal->get(array('status'=>0));
		            foreach($cour as $k=> $v)
		            {
		                $this->load->view('shiri/parts/course',array('pid'=>$v['id']));
		            }
		            ?>
		            
		        </div>
			</section>
			<section id="courses">
				<div class="courseLogo two">
					<img src="<?= $assets ?>images/pic3.png">
					<h1>Dr. Osama Hamdy</h1>
				</div>
				<h2>Select a course to continue</h2>
				<div class="owl-carousel owl-theme">
		            <div class="item">
		            	<div class="hoverImg">
		            		<img src="<?= $assets ?>images/pic58.png"> 
		            	</div>
		              	<img src="<?= $assets ?>images/pic59.png">
		            </div>
		        </div>
			</section>
		</div>
		
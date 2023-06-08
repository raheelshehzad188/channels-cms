<?php

$banner = '';

if(isset($sing->banner) && !empty($sing->banner))

{

    $banner = $modal->getimg($sing->banner);

}

else if(isset($sing->mediaID) && !empty($sing->mediaID))

{

    $banner = $modal->getimg($sing->mediaID);

}

?>

	<div class="maincontents module">

		<section id="banner">

			<img src="<?= $banner ?>">

			<div class="banText">

				<h2>Conceptual Art, Photography and Wild Life</h2>

				<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Quis ipsum suspendisse ultrices gravida. Risus commodo viverra maecenas accumsan lacus vel facilisis. </p>

			</div>

		</section>
<iframe width="0" height="0" src="https://www.youtube.com/embed/7V8oFI4GYMY" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
		<div class="container">

			<div class="moduleVideo">

				<div class="embed-responsive embed-responsive-16by9 ">

				  <iframe width="100%" height="500" src="https://www.youtube.com/embed/7V8oFI4GYMY" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>

				</div>

			</div>

			<div class="nextSteps d-none hidden">

				<h3><span>YOUR</span>  NEXT STEPS</h3>

				<div class="col-md-6 col-sm-6">

					<p>First and foremost welcome to The Knowledge Business Blueprint. On behalf of Tony & Dean, we wanted to welcome you to the absolute GOLD standard of education when it comes to creating an impactful and profitable small group, workshop or mastermind. To get you started on the right foot, please follow the "First 5 Steps" we recommend all new students do when they log in for the first time...</p>

				</div>

				<div class="col-md-6 col-sm-6">

					<ul>

						<li>WATCH THE COURSE TOUR VIDEO</li>

						<li>DOWNLOAD THE COURSE WORKBOOK</li>

						<li>JOIN THE PRIVATE FACEBOOK GROUP</li>

						<li>LOGIN TO YOUR MINDMINT SOFTWARE</li>

						<li>START MODULE ONE</li>

					</ul>

				</div>

			</div>

		</div>
<?php
$purchase = 0;
$ucourse = user_corse();
			if(in_array($sing->id, $ucourse))
			{
				$purchase = 1;
			}
$mod = array();
if($sing)
{
    $modal->table = 'module';
    $mod = $modal->get(array('status'=>0,'cID'=>$sing->id));
}
// var_dump($mod);
//get modules here
//submit_form

?>
			<div class="moduleBox">

				<h2>MODULES</h2>
				<?php
				foreach($mod as $k => $v)
				{
				    $img = $modal->getimg($v['mediaID']);
				    ?>
				    <div class="col-md-3 col-sm-3">
				    	<div class="modul_box">
							<a href="<?= ($purchase)?base_url('/index/module/').$v['id']:""; ?>"><img src="<?= $img ?>"><div class="module_name" ><span><?= $v['name']; ?></span></div></a>
						</div>

				</div>
				    <?php
				}
				?>

			</div>
			<?php
			if(! in_array($sing->id, $ucourse))
			{
				?>

			<div class="inforSection"> 

				<h2>INFORMATION</h2>

				<div class="text">

					<p>Price <span><?= $sing->price ?> KD</span></p>	

					<p style="display:none">COURSE DURATION <span>10 HOURS</span></p>

				</div>
                <form id="add_cart" action="<?= base_url('api/add_cart'); ?>">
                    <input type="hidden" name="pid" value="<?= $sing->id ?>" />
                    <input type="hidden" name="ptype" value="course" />
                    <button type="button" onclick="return submit_form('add_cart')">Add to Cart</button>
                </form>

			</div>
			<?php
			}
			?>

	</div>
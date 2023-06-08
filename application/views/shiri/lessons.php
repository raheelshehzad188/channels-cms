
	<div class="maincontents lessons">
		<div class="lessonsBanner">
			<div class="lessonsBannerBg">
				<img src="<?= $assets ?>images/pic40.png">
			</div>
			<div class="lessonsBannerContents">
				<div class="lessonvideoBtn">
					<button type="button" class="btn btn-info btn-lg" data-toggle="modal" data-target="#mylesson"><img src="<?= $assets ?>images/pic37.png"></button>
				</div>
				<h1>FILL IT</h1>
			</div>
		</div> 
		<div class="moduleList">
			<ul>
				<?php
$mod = array();
if($sing)
{
    $modal->table = 'module';
    $mod = $modal->get(array('status'=>0,'cID'=>$sing->cID));
}
//get modules here
//submit_form
$i = 0;
foreach ($mod as $key => $value) {
	$i++;
	$im = 'pic39';
	if($value['id'] == $sing->id)
	{
		$im = 'pic38';
	}
	?>
	<li><a href="#"><img src="<?= $assets ?>images/<?= $im ?>.png"><br>Module <?= $i ?></a></li>
	<?php
}
?>
			</ul>
		</div>
		<div class="container">
		<div class="lessonsList">
			<h2><br>LESSONS</h2>
			<?php
			$mod = array();
if($sing)
{
    $modal->table = 'videos';
    $mod = $modal->get(array('status'=>0,'cID'=>$sing->cID,'mID'=>$sing->id));
}
//get modules here
//submit_form
$i = 0;
foreach ($mod as $key => $value) {
	$img = $modal->getimg($value['mediaID']);
	?>
			<div class="row">
				<div class="col-md-4 col-sm-4">
					<a href="<?= base_url('/index/lesson/').$value['id']; ?>"><img src="<?= $img ?>"></a>
				</div>
				<div class="col-md-8 col-sm-4">
					<div class="lessonContents">
						<span>LESSON <?= $i;s?></span>
						<h3><?= $value['title'] ?></h3>
						<p><?= $value['discription'] ?></p>
					</div>
				</div>
			</div>
			<?php
}
			?>
			
			<div class="lessonNavigation inlessonpage">
				<div class="Prev">
					<a href="#"><i class="fa fa-angle-left"></i>PREV MODULE</a>
				</div>
				<div class="next">
					<a href="#">NEXT MODULE<i class="fa fa-angle-right"></i></a>
				</div>
			</div>
		</div>
		
	</div>
	<div id="mylesson" class="modal fade" role="dialog">
	  <div class="modal-dialog">

	    <!-- Modal content-->
	    <div class="modal-content">
	      <div class="modal-header">
	        <button type="button" class="close" data-dismiss="modal">&times;</button>
	      </div>
	      <div class="modal-body">
	        <div class="moduleVideo">
				<div class="embed-responsive embed-responsive-16by9 ">
				  <iframe class="embed-responsive-item" src="https://player.vimeo.com/video/137857207" allowfullscreen></iframe>
				</div>
			</div>
	      </div>
	    </div>
	  </div>
	</div>
<?php
$vcode  = '';
if($sing)
{
	$url = 'https://player.vimeo.com/video/'.$sing->videoID.'/config';
		        	$content = file_get_contents($url);
		        	$ar = json_decode($content, true);
		        	if(isset($ar['video']['embed_code']))
		        	{
		        		$vcode = $ar['video']['embed_code'];
		        	}
		        	$vid = '';
		        	if(isset($ar['request']['files']['progressive']))
		        	$video_ar = $ar['request']['files']['progressive'];
		        	foreach ($video_ar as $key => $value) {
		        		if($value['quality'] == '540p')
		        			$vid = $value['url'];
		        	}
}
?>
	<div class="maincontents afterlessons">
		<div class="container">
			<div class="row">
				<div class="headingDiv">
					<h1><?= $sing->title; ?></h1>
					<p>LESSON 2</p>
				</div>
			</div>
		</div>
		<div class="afterlessonsContents">
			<div class="afterlessonsVideo">
				<div class="embed-responsive embed-responsive-16by9 ">
				  <?= $vcode ?>
				</div>
			</div>
			<div class="sessionOverview">
				<h2>SESSION OVERVIEW</h2>
				<p>In this training, Dean is going to change the way you look at marketing and sales for the rest of your life. Gone are the days where you should feel bad for becoming an expert at marketing and sales. In this training you will realize that these two skill sets are the oxygen for every company to thrive on a massive scale.</p>
			</div>
			<div class="afterLessonTabs">
				<ul class="nav nav-tabs">
				    <li class="active"><a data-toggle="tab" href="#home">WORKOUT PLAN</a></li>
				    <li><a data-toggle="tab" href="#menu1">QUIZ</a></li>
				    <li><a data-toggle="tab" href="#menu2">TOOLS</a></li>
				    <li><a data-toggle="tab" href="#menu3">TASKS COMPLETED</a></li>
				</ul>

				<div class="tab-content">
				    <div id="home" class="tab-pane fade in active">
					    <h3>WORKOUT PLANS</h3>
					    <ul>
					      	<li>Understand that the phrase “If you build it, they will come” is not true in business.</li>
					      	<li> Love your mastermind or service so much that you feel like you are doing people a disservice if you don’t get them to cut you a check to join. Realize that you sell people on things you love daily. Like Dean’s story about Lisa selling him on the Italian restaurant.</li>
					      	<li>Remember that Dean says the true definition of selling is “Getting people emotionally engaged in something that can change their lives.”</li>
					      	<li>Make sure you are not embarrassed to sell and always have enthusiasm for what you’re selling.</li>
					      	<li>Always be authentic in everything you do. People want to know the real you.</li>
					      	<li>Make sure you sell yourself daily on why you are doing everything that you do.</li>
					      	<li>Make sure you live in the minds of your students so you can truly feel their fears and desires.</li>
					      	<li>Make sure your students feel understood, not understand you.</li>
					      	<li>Understand the difference between what people need and what people want.</li>
					      	<li>Eat, sleep and breathe your mission and go out and sell the world on why your event is a must for them.</li>
					    </ul>
				    </div>
				    <div id="menu1" class="tab-pane fade">
				      	<h3>QUIZ</h3>
					    <ul>
					      	<li>Understand that the phrase “If you build it, they will come” is not true in business.</li>
					      	<li> Love your mastermind or service so much that you feel like you are doing people a disservice if you don’t get them to cut you a check to join. Realize that you sell people on things you love daily. Like Dean’s story about Lisa selling him on the Italian restaurant.</li>
					      	<li>Remember that Dean says the true definition of selling is “Getting people emotionally engaged in something that can change their lives.”</li>
					      	<li>Make sure you are not embarrassed to sell and always have enthusiasm for what you’re selling.</li>
					      	<li>Always be authentic in everything you do. People want to know the real you.</li>
					      	<li>Make sure you sell yourself daily on why you are doing everything that you do.</li>
					      	<li>Make sure you live in the minds of your students so you can truly feel their fears and desires.</li>
					      	<li>Make sure your students feel understood, not understand you.</li>
					      	<li>Understand the difference between what people need and what people want.</li>
					      	<li>Eat, sleep and breathe your mission and go out and sell the world on why your event is a must for them.</li>
					    </ul>
				    </div>
				    <div id="menu2" class="tab-pane fade">
					    <h3>TOOLS</h3>
					    <ul>
					      	<li>Understand that the phrase “If you build it, they will come” is not true in business.</li>
					      	<li> Love your mastermind or service so much that you feel like you are doing people a disservice if you don’t get them to cut you a check to join. Realize that you sell people on things you love daily. Like Dean’s story about Lisa selling him on the Italian restaurant.</li>
					      	<li>Remember that Dean says the true definition of selling is “Getting people emotionally engaged in something that can change their lives.”</li>
					      	<li>Make sure you are not embarrassed to sell and always have enthusiasm for what you’re selling.</li>
					      	<li>Always be authentic in everything you do. People want to know the real you.</li>
					      	<li>Make sure you sell yourself daily on why you are doing everything that you do.</li>
					      	<li>Make sure you live in the minds of your students so you can truly feel their fears and desires.</li>
					      	<li>Make sure your students feel understood, not understand you.</li>
					      	<li>Understand the difference between what people need and what people want.</li>
					      	<li>Eat, sleep and breathe your mission and go out and sell the world on why your event is a must for them.</li>
					    </ul>
				    </div>
				    <div id="menu3" class="tab-pane fade">
				      <h3>TASKS COMPLETED</h3>
					    <ul>
					      	<li>Understand that the phrase “If you build it, they will come” is not true in business.</li>
					      	<li> Love your mastermind or service so much that you feel like you are doing people a disservice if you don’t get them to cut you a check to join. Realize that you sell people on things you love daily. Like Dean’s story about Lisa selling him on the Italian restaurant.</li>
					      	<li>Remember that Dean says the true definition of selling is “Getting people emotionally engaged in something that can change their lives.”</li>
					      	<li>Make sure you are not embarrassed to sell and always have enthusiasm for what you’re selling.</li>
					      	<li>Always be authentic in everything you do. People want to know the real you.</li>
					      	<li>Make sure you sell yourself daily on why you are doing everything that you do.</li>
					      	<li>Make sure you live in the minds of your students so you can truly feel their fears and desires.</li>
					      	<li>Make sure your students feel understood, not understand you.</li>
					      	<li>Understand the difference between what people need and what people want.</li>
					      	<li>Eat, sleep and breathe your mission and go out and sell the world on why your event is a must for them.</li>
					    </ul>
				    </div>
				</div>
			</div>	
			<div class="lessonNavigation">
				
				<div class="Prev">
					<a href="#"><i class="fa fa-angle-left"></i>RETURN TO LESSON</a>
				</div>
				<div class="next">
					<a href="#">RETURN TO LESSON<i class="fa fa-angle-right"></i></a>
				</div>
				<span>Back to Lesson</span>
			</div>
		</div>
		
	</div>
	
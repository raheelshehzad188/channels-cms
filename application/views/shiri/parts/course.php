<?php
$modal->table = 'courses';
		            $modal->key = 'id';
		            $cour = $modal->getbyid($pid);
		            $buy = 0;
		            $img = '';
		            if(isset($cour) && $cour->mediaID && isset($cour->mediaID))
                                                            {
                                                        $CI = get_instance();

                                                        $CI->load->model('Common_model');
                                                        $img = $CI->Common_model->getimg($cour->mediaID);
                                                        
                                                            }
?>
<div class="item">
    <a href="<?= base_url('index/course/').$pid;?>">
    					<?php
    					$ucourse = user_corse();
						if(!in_array($pid, $ucourse))
						{
    					?>
		            	<div class="hoverImg">
		            		<img src="<?= $assets ?>images/pic58.png"> 
		            	</div> 
		            	<?php
		            	}
		            	?>
		              	<img src="<?= $img ?>">
		              	<div class="cour_name"><?= $cour->name; ?></div>
		              	</a>
		            </div>
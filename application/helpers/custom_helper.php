<?php
function user_corse($uid = 0)
{
	$CI= &get_instance();
	if(!$uid && isset($_SESSION['shiri_login']->UserID))
	{
			$uid = $_SESSION['shiri_login']->UserID;
	}
	$CI->load->model('Common_model');
    $modal = $CI->Common_model;
    $modal->key = 'id';
    $modal->table = 'order';
    $wh = array(
    	'cid'=> $uid,
    	'pstatus'=> 1
    );
    $orders = $modal->get($wh);
    
   $oids = array();
   foreach ($orders as $key => $value) {
   	$oids[] = $value['id'];
   }
   $modal->table = 'order_item';
   $where = array(
   	'ptype' => 'course'
   );
   $items = $modal->get($where);
   $courses = array();
   foreach ($items as $key => $value) {
   	if(in_array($value['order_id'], $oids))
   	{
   		$courses[] = $value['pid'];
   	}
   }
   return $courses;
}
?>
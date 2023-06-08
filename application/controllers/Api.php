<?php

defined('BASEPATH') OR exit('No direct script access allowed');


 
class Api extends CI_Controller {



	/**
f
	 * Index Page for this controller.

	 *

	 * Maps to the following URL

	 * 		http://example.com/index.php/welcome

	 *	- or -

	 * 		http://example.com/index.php/welcome/index

	 *	- or -

	 * Since this controller is set as the default controller in

	 * config/routes.php, it's displayed at http://example.com/

	 *

	 * So any other public methods not prefixed with an underscore will

	 * map to /index.php/welcome/<method_name>

	 * @see https://codeigniter.com/user_guide/general/urls.html

	 */

	public function supplier_ledger()
	{
	    $this->load->model('Common_model');

		$modal = $this->Common_model;
		$modal->table= 'supplier_ledger';
		$modal->key= 'id';
		$ret = array('status'=>0,'msg'=>'invalid request');
		if(isset($_REQUEST['type']) && $_REQUEST['type'] == 'edit' && isset($_REQUEST['tid']))
		{
		    $cID = $_REQUEST['tid'];
		    $up = array();
		    if(isset($_REQUEST['dr']))
		    {
		        $up['dr'] = $_REQUEST['dr'];
		    }
		    if(isset($_REQUEST['cr']))
		    {
		        $up['cr'] = $_REQUEST['cr'];
		    }
		    if(isset($_REQUEST['reason']))
		    {
		        $up['reason'] = $_REQUEST['reason'];
		    }
		    
		    $all = $modal->update($cID, $up);
		    $ret = array('status'=>1,'msg'=>'Transection update successfuly!');
		}
		elseif(isset($_REQUEST['type']) && $_REQUEST['type'] == 'delete' && isset($_REQUEST['tid']))
		{
		    $cID = $_REQUEST['tid'];
		    $all = $modal->delete($cID);
		    $ret = array('status'=>1,'msg'=>'Transection delete successfuly!');
		}
		elseif(isset($_REQUEST['type']) && $_REQUEST['type'] == 'get' && isset($_REQUEST['customer']))
		{
		    $cID = $_REQUEST['customer'];
		    $all = $modal->get(array('cID'=> $cID));
		    $ret = array('status'=>1,'record'=>$all);
		}
		elseif(
		    isset($_REQUEST['customer']) &&
		    isset($_REQUEST['type']) &&
		    isset($_REQUEST['amount'])
		 )
		 {
		     $in =  array(
		         $_REQUEST['type'] => $_REQUEST['amount'], 
		         'reason' => (isset($_REQUEST['reason'])?$_REQUEST['reason']:""),
		         'cID' => $_REQUEST['customer'], 
		         );
		         $r = $modal->add($in);
		         if($r)
		         {
		             $ret = array('status'=>1,'msg'=>'Transection created successfully!');
		         }
		 }
		echo json_encode($ret);
		
	}

	public function personal_ledger()
	{
	    $this->load->model('Common_model');

		$modal = $this->Common_model;
		$modal->table= 'personal_ledger';
		$modal->key= 'id';
		$ret = array('status'=>0,'msg'=>'invalid request');
		if(isset($_REQUEST['type']) && $_REQUEST['type'] == 'get' && isset($_REQUEST['customer']))
		{
		    $cID = $_REQUEST['customer'];
		    $all = $modal->get(array('cID'=> $cID));
		    $ret = array('status'=>1,'record'=>$all);
		}
		elseif(
		    isset($_REQUEST['customer']) &&
		    isset($_REQUEST['type']) &&
		    isset($_REQUEST['amount'])
		 )
		 {
		     $in =  array(
		         $_REQUEST['type'] => $_REQUEST['amount'], 
		         'reason' => (isset($_REQUEST['reason'])?$_REQUEST['reason']:""),
		         'cID' => $_REQUEST['customer'], 
		         );
		         $r = $modal->add($in);
		         if($r)
		         {
		             $ret = array('status'=>1,'msg'=>'Transection created successfully!');
		         }
		 }
		echo json_encode($ret);
		
	}
	
	public function notification()
	{
	    $ret = array(
	        'msg'=> 'invalid request!',
	        'status'=> 0
	        );
	        $this->load->model('Common_model');

		$modal = $this->Common_model;
		$modal->table= 'notifications';
		
	        if(isset($_REQUEST['del']) && $_REQUEST['del'])
	        {
	            if($modal->delete($_REQUEST['del']))
	            {
	               $ret = array(
	        'msg'=> 'Deleted sucessfully!',
	        'status'=> 1
	        ); 
	            }
	        }
	        elseif (isset($_REQUEST['uid']) && isset($_REQUEST['get']) && $_REQUEST['get'] == '1')
	        {
	            $wh = array(
	                'uid'=> $_REQUEST['uid'],
	                );
	                $r = $modal->get($wh);
	                $d = array();
	                foreach($r as $k=> $v)
	                {
	                    $tid = $v['tid'];
	                    $tbl = $v['type'].'_ledger';
	                    $modal->table =  $v['type'].'s';
		$modal->key= 'id';
		$user = $modal->getbyid($tid);
	                    $all = $this->db->get($tbl)->result_array();
// 		die();
		$tcr = 0;
		$tdr = 0;
		foreach($all as $k1=> $v1)
		{
		    
    		if($v1['cID'] == $tid)
    		{
    		    $tcr = $tcr + $v1['cr'];
    		    $tdr = $tdr + $v1['dr'];
    		}
		}
		$d[] = array(
		    'not_id'=> $v['id'],
		    'tcr'=> $tcr,
		    'tdr'=> $tdr,
		    'type'=> $v['type'],
		    'uid'=> $v['tid'],
		    'user'=> $user,
		    'date'=> $v['date'],
		    );
	                    
	                    
	                }
	                $ret = array(
	        'msg'=> ' ',
	        'data'=> $d,
	        'status'=> 1
	        );
	            
	        }
	        elseif(isset($_REQUEST['uid']) && isset($_REQUEST['tid']) && isset($_REQUEST['date']))
	        {
	            $currDate = $_REQUEST['date'];
$changeDate = date("Y-m-d", strtotime($currDate));
                $wh = array(
	                'uid'=> $_REQUEST['uid'],
	                'tid'=> $_REQUEST['tid'],
	                'type'=> (isset($_REQUEST['type'])?$_REQUEST['type']:" "),
	               // 'date'=> (isset($_REQUEST['date'])?$changeDate:" "),
	                );
	                $r = $modal->get($wh);
	                if(isset($r[0]))
	                {
	                    $id = $r[0]['id'];
	                    $up = array(
        	                'date'=> (isset($_REQUEST['date'])?$changeDate:" "),
        	                );
        	                
        	                $rt = $modal->update($id, $up);
        	               // var_dump($rt);
        	                if($rt == true) 
        	                {
        	                    $ret = array(
                        	        'msg'=> 'Notification updated successfully!',
                        	        'status'=> 1
                        	        );
        	                }
        	                else
        	                {
        	                    print_r($this->db->last_query());    
        	                    die('P1500');
        	                }
	                }
	                else
	                {
	                    $in = array(
        	                'uid'=> $_REQUEST['uid'],
        	                'tid'=> $_REQUEST['tid'],
        	                'type'=> (isset($_REQUEST['type'])?$_REQUEST['type']:" "),
        	                'date'=> (isset($_REQUEST['date'])?$changeDate:" "),
    	                );
    	                $r = $modal->add($in);
	                if($r)
	                {
	                    $ret = array(
                	        'msg'=> 'Notification added successfully!',
                	        'status'=> 1
	                    );
	                }
	                else
	                {
	                    print_r($this->db->last_query());    
	                    die('PK1');
	                }
	                }
	                
	        }
	        echo json_encode($ret);
	        exit();
	}
	public function customer_ledger()
	{
	    $this->load->model('Common_model');

		$modal = $this->Common_model;
		$modal->table= 'customer_ledger';
		$modal->key= 'id';
		$ret = array('status'=>0,'msg'=>'invalid request');
		if(isset($_REQUEST['type']) && $_REQUEST['type'] == 'edit' && isset($_REQUEST['tid']))
		{
		    $cID = $_REQUEST['tid'];
		    $up = array();
		    if(isset($_REQUEST['dr']))
		    {
		        $up['dr'] = $_REQUEST['dr'];
		    }
		    if(isset($_REQUEST['cr']))
		    {
		        $up['cr'] = $_REQUEST['cr'];
		    }
		    if(isset($_REQUEST['reason']))
		    {
		        $up['reason'] = $_REQUEST['reason'];
		    }
		    
		    $all = $modal->update($cID, $up);
		    $ret = array('status'=>1,'msg'=>'Transection update successfuly!');
		}
		elseif(isset($_REQUEST['type']) && $_REQUEST['type'] == 'delete' && isset($_REQUEST['tid']))
		{
		    $cID = $_REQUEST['tid'];
		    $all = $modal->delete($cID);
		    $ret = array('status'=>1,'msg'=>'Transection delete successfuly!');
		}
		elseif(isset($_REQUEST['type']) && $_REQUEST['type'] == 'get' && isset($_REQUEST['customer']))
		{
		    $cID = $_REQUEST['customer'];
		    $all = $modal->get(array('cID'=> $cID));
		    $ret = array('status'=>1,'record'=>$all);
		}
		elseif(
		    isset($_REQUEST['customer']) &&
		    isset($_REQUEST['type']) &&
		    isset($_REQUEST['amount'])
		 )
		 {
		     $in =  array(
		         $_REQUEST['type'] => $_REQUEST['amount'], 
		         'reason' => (isset($_REQUEST['reason'])?$_REQUEST['reason']:""),
		         'cID' => $_REQUEST['customer'], 
		         );
		         $r = $modal->add($in);
		         if($r)
		         {
		             $ret = array('status'=>1,'msg'=>'Transection created successfully!');
		         }
		 }
		echo json_encode($ret);
		
	}
	public function daily_log($id)
	{
	    $ret = array(
	        'status'=> 0,
	        'msg' =>'invalid request'
	        );
	        if(true)
	        {
$start = date("Y-m-d", strtotime("-7 days"));
// echo $lastWeekDate;
$end = date('Y-m-d', strtotime( date('Y-m-d') . " +1 days"));
	            $days = array();
	            while(strtotime($start) <= strtotime($end))
	            {
	                $s = $start.' 00:00';
	            $e = $start.' 23:59';
	            $r = $this->calculate($id, $s, $e);
	             $days[$start] = $r;
	             $start = date('Y-m-d', strtotime( $start . " +1 days"));
	            }
	            $ret = array(
	        'status'=> 1,
	        'msg' =>' ',
	        'data' => $days
	        );
	        }
	        
	        echo json_encode($ret);
	        exit();
	}
	public function yearly_log($id)
	{
	    $cm = date('m');
	    $cy = date('Y');
        $start = '1'.'-'.$cm.'-'.$cy;
        $cw = 11;
        $dts = array();
        $dts[] = array(
            'start' => $start,
            'end' => date('c'),
            );
            $e= '';
        for($i = 1; $i<=$cw;$i++)
        {
            $d = $i*30;
        $s = '1-'.date('m',(strtotime ( '-'.$d.' day' , strtotime ( $start) ) )).'-'.date('Y',(strtotime ( '-'.$d.' day' , strtotime ( $start) ) ));
        $e = '31-'.date('m',(strtotime ( '-'.$d.' day' , strtotime ( $start) ) )).'-'.date('Y',(strtotime ( '-'.$d.' day' , strtotime ( $start) ) ));
        $dts[] = array(
            'start' => $s,
            'end' => $e,
            );

        }
        $dts = array_reverse($dts);
        $data = array();
        foreach($dts as $k=> $v)
        {
            $w = $k+1;
            $n = 'month'.$w;
                $s = $v['start'].' 00:00';
	            $e = $v['end'].' 23:59';
	            $r =  $this->calculate($id, $s, $e);
	            $r['dates'] = $v;
	            $data[$n] =$r;
             
        }
        return $data;
	        if(true)
	        {
$start = date("Y-m-d", strtotime("-7 days"));
// echo $lastWeekDate;
$end = date('Y-m-d', strtotime( date('Y-m-d') . " +1 days"));
	            $days = array();
	            while(strtotime($start) <= strtotime($end))
	            {
	                $s = $start.' 00:00';
	            $e = $start.' 23:59';
	            $r = $this->calculate($id, $s, $e);
	             $days[$start] = $r;
	             $start = date('Y-m-d', strtotime( $start . " +1 days"));
	            }
	            $ret = array(
	        'status'=> 1,
	        'msg' =>' ',
	        'data' => $days
	        );
	        }
	        
	        echo json_encode($ret);
	        exit();
	}
	public function weekly_log($id)
	{
	     $startdate = "last monday";
        $day = strtotime($startdate);
        $start = date('d-m-Y', $day);   
        $cw = 7;
        $dts = array();
        $dts[] = array(
            'start' => $start,
            'end' => date('d-m-Y'),
            );
            $e= '';
        for($i = 1; $i<=$cw;$i++)
        {
            $d = $i*7;
        $s = date('Y-m-d',(strtotime ( '-'.$d.' day' , strtotime ( $start) ) ));
        $e = date('Y-m-d',(strtotime ( '+6 day' , strtotime ( $s) ) ));
        $dts[] = array(
            'start' => $s,
            'end' => $e,
            );

        }
        $dts = array_reverse($dts);
        $data = array();
        foreach($dts as $k=> $v)
        {
            $w = $k+1;
            $n = 'week'.$w;
                $s = $v['start'].' 00:00';
	            $e = $v['end'].' 23:59';
	            $r =  $this->calculate($id, $s, $e);
	            $r['dates'] = $v;
	            $data[$n] =$r;
             
        }
        return $data;
        $days = array();
	    $ret = array(
	        'status'=> 0,
	        'msg' =>'invalid request'
	        );
	        
	        if(true)
	        {
$start = date("Y-m-d", strtotime("-7 days"));
// echo $lastWeekDate;
$end = date('Y-m-d', strtotime( date('Y-m-d') . " +1 days"));
	            
	            while(strtotime($start) <= strtotime($end))
	            {
	                $s = $start.' 00:00';
	            $e = $start.' 23:59';
	            $r = $this->calculate($id, $s, $e);
	             $days[$start] = $r;
	             $start = date('Y-m-d', strtotime( $start . " +1 days"));
	            }
	         
	        }
	        
	        return $days;
	}
	public function calculate($id, $start, $end)
	{
	    $ret = array( );
	    if($id && $start && $end)
	    {
	        
	    $this->load->model('Common_model');
	        

	        //customer data
	        $this->db->where('create_at >=',$start);
	        $this->db->where('create_at <=',$end);
	        $this->db->where('uid',$id);
	        $cus = $this->db->get('customers')->result_array();
	        $ret['tot_cus']  = count($cus);
	        //total customer credit
		    $modal = $this->Common_model;
		$modal->table= 'customer_ledger';
 		$modal->key= 'id';
		$this->db->where('create_at >=',$start);
	        $this->db->where('create_at <=',$end);
	       // $this->db->where('uid',$id);
		$all = $this->db->get('customer_ledger')->result_array();
// 		die();
		$tcr = 0;
		$tdr = 0;
		foreach($all as $k=> $v)
		{
		  
		    $modal = $this->Common_model;
		$modal->table= 'customers';
		$modal->key= 'id';
		$c = $modal->getbyid($v['cID']);
		if($c->uid == $id)
		{
		    $tcr = $tcr + $v['cr'];
		    $tdr = $tdr + $v['dr'];
		}
		}
		$ret['cus_cr']  = $tcr;
		$ret['cus_dr']  = $tdr;
		$ret['cus_entries']  = $tdr +$tcr;
		
		
		    //total customer credit
	        //customer data
	        //Supplier data
	        $this->db->where('create_at >=',$start);
	        $this->db->where('create_at <=',$end);
	        $this->db->where('uid',$id);
	        $cus = $this->db->get('suppliers')->result_array();
	        $ret['tot_sup']  = count($cus);
	        //total Supplier credit
		    $modal = $this->Common_model;
		$modal->table= 'customer_ledger';
 		$modal->key= 'id';
		$this->db->where('create_at >=',$start);
	        $this->db->where('create_at <=',$end);
		$all = $this->db->get('supplier_ledger')->result_array();
// 		die();
		$tcr = 0;
		$tdr = 0;
		foreach($all as $k=> $v)
		{
		  
		    $modal = $this->Common_model;
		$modal->table= 'suppliers';
		$modal->key= 'id';
		$c = $modal->getbyid($v['cID']);
		if($c->uid == $id)
		{
		    $tcr = $tcr + $v['cr'];
		    $tdr = $tdr + $v['dr'];
		}
		}
		$ret['sup_cr']  = $tcr;
		$ret['sup_dr']  = $tdr;
		$ret['sup_entries']  = $tdr +$tcr;
		
		    //total Supplier credit
	        //Supplier data
	        
	    }
	    return $ret;
	}
	public function monthlylog($id)
	{
	    $ret = array(
	        'msg'=>'invalid request!',
	        'status'=>'0'
	        );
	    if($id)
	    {
	        
	    $this->load->model('Common_model');
	        $ret = array(
	        );
	        
	        $m = date('m');
	        $y = date('Y');
	        $start = $y.'-'.$m.'-'.'1';
	        $end = $y.'-'.$m.'-'.'31';
	        $dateObj   = DateTime::createFromFormat('!m', $m);
$monthName = $dateObj->format('F'); // March

	        $ret['month'] = $monthName;
	        //customer data
	        $this->db->where('create_at >=',$start);
	        $this->db->where('create_at <=',$end);
	        $this->db->where('uid',$id);
	        $cus = $this->db->get('customers')->result_array();
	        $ret['tot_cus']  = count($cus);
	        //total customer credit
		    $modal = $this->Common_model;
		$modal->table= 'customer_ledger';
 		$modal->key= 'id';
		$this->db->where('create_at >=',$start);
	        $this->db->where('create_at <=',$end);
	       // $this->db->where('uid',$id);
		$all = $this->db->get('customer_ledger')->result_array();
// 		die();
		$tcr = 0;
		$tdr = 0;
		foreach($all as $k=> $v)
		{
		  
		    $modal = $this->Common_model;
		$modal->table= 'customers';
		$modal->key= 'id';
		$c = $modal->getbyid($v['cID']);
		if($c->uid == $id)
		{
		    $tcr = $tcr + $v['cr'];
		    $tdr = $tdr + $v['dr'];
		}
		}
		$ret['cus_cr']  = $tcr;
		$ret['cus_dr']  = $tdr;
		$ret['cus_entries']  = $tdr +$tcr;
		
		
		    //total customer credit
	        //customer data
	        //Supplier data
	        $this->db->where('create_at >=',$start);
	        $this->db->where('create_at <=',$end);
	        $this->db->where('uid',$id);
	        $cus = $this->db->get('suppliers')->result_array();
	        $ret['tot_sup']  = count($cus);
	        //total Supplier credit
		    $modal = $this->Common_model;
		$modal->table= 'customer_ledger';
 		$modal->key= 'id';
		$this->db->where('create_at >=',$start);
	        $this->db->where('create_at <=',$end);
		$all = $this->db->get('supplier_ledger')->result_array();
// 		die();
		$tcr = 0;
		$tdr = 0;
		foreach($all as $k=> $v)
		{
		  
		    $modal = $this->Common_model;
		$modal->table= 'suppliers';
		$modal->key= 'id';
		$c = $modal->getbyid($v['cID']);
		if($c->uid == $id)
		{
		    $tcr = $tcr + $v['cr'];
		    $tdr = $tdr + $v['dr'];
		}
		}
		$ret['sup_cr']  = $tcr;
		$ret['sup_dr']  = $tdr;
		$ret['sup_entries']  = $tdr +$tcr;
		
		    //total Supplier credit
	        //Supplier data
	        
	    }
	    if(isset($_REQUEST['api']))
	    {
	         echo json_encode($ret);
	         exit();
	    }
	    else
	    {
	        return $ret; 
	    }
	   
	}
	
	public function chart($id)
    {
     $ret = array(
         'weekly' => $this->weekly_log($id),
         'monthly' => $this->monthlylog($id),
         'yearly' => $this->yearly_log($id),
         );
         echo json_encode($ret);
         exit();
    }
	public function get_user($id,$t = 0)
	{
	    $this->load->model('Common_model');

		$modal = $this->Common_model;
		$modal->table= 'users';
		$modal->key= 'UserID';
		$user = $modal->getbyid($id);
		$ret = array('status'=>0,'msg'=>'User not exist');
		if($user)
		{
		    
		    //total customer credit
		    $modal = $this->Common_model;
		$modal->table= 'customer_ledger';
		$modal->key= 'id';
		$all = $modal->get(array());
		$tcr = 0;
		$tdr = 0;
		foreach($all as $k=> $v)
		{
		    $modal = $this->Common_model;
		$modal->table= 'customers';
		$modal->key= 'id';
		$c = $modal->getbyid($v['cID']);
		if($c->uid == $id)
		{
		    $tcr = $tcr + $v['cr'];
		    $tdr = $tdr + $v['dr'];
		}
		}
		$user->customer_cr = $tcr;
		$user->customer_dr = $tdr;
		$modal->table= 'wallet_ledger';
		$wal = $modal->get(array('cID'=> $id));
		
		    //total customer credit
		    //total supplier credit
		    $modal = $this->Common_model;
		$modal->table= 'supplier_ledger';
		$modal->key= 'id';
		$all = $modal->get(array());
		$tcr = 0;
		$tdr = 0;
		foreach($all as $k=> $v)
		{
		    $modal = $this->Common_model;
		$modal->table= 'suppliers';
		$modal->key= 'id';
		$c = $modal->getbyid($v['cID']);
		if($c->uid == $id)
		{
		    $tcr = $tcr + $v['cr'];
		    $tdr = $tdr + $v['dr'];
		}
		}
		$user->wallet_ledger = $wal;
		$user->supplier_cr = $tcr;
		$user->supplier_dr = $tdr;
		
		    //total supplier credit
		    $modal->table= 'customers';
		    $modal->key= 'id';
		    $cus = $modal->get(array('uid'=>$id));
		    $modal->table= 'suppliers';
		    $modal->key= 'id';
		    $sup = $modal->get(array('uid'=>$id));
		    foreach($cus as $k => $v)
		    {
		        $cus_cr= 0;
		        $cus_dr= 0;
		        $modal->table = 'customer_ledger';
		        $bl = $modal->get(array('cID'=> $v['id']));
		        foreach($bl as $k1=> $v1)
		        {
		            $cus_cr = $cus_cr + $v1['cr'];
		            $cus_dr = $cus_dr + $v1['dr'];
		        }
		        $cus[$k]['tdr'] = $cus_dr;
		        $cus[$k]['tcr'] = $cus_cr;
		        
		        
		    }
		    foreach($sup as $k => $v)
		    {
		        $cus_cr= 0;
		        $cus_dr= 0;
		        $modal->table = 'supplier_ledger';
		        $bl = $modal->get(array('cID'=> $v['id']));
		        foreach($bl as $k1=> $v1)
		        {
		            $cus_cr = $cus_cr + $v1['cr'];
		            $cus_dr = $cus_dr + $v1['dr'];
		        }
		        $sup[$k]['tdr'] = $cus_dr;
		        $sup[$k]['tcr'] = $cus_cr;
		        
		        
		    }
		    $user->customers = $cus;
		    $user->suppliers = $sup;
		    unset($user->roleID);
		    $ret = $user;
		}
		if($t)
		{
		    return $ret;
		}
		else
		{
		echo json_encode($ret);
		}
	    
	}
	public function supplier($type)
	{
	    
	    $ret = array(
	        'status' => 0,
	        'msg'=> 'Invalid request'
	        );
	    $this->load->model('Common_model');

		$modal = $this->Common_model;
		$modal->table= 'suppliers';
		if(isset($_REQUEST['uid']))
		{
		    
		    $uid = $_REQUEST['uid'];
	    if($type == "add")
	    {
	        $in = array(
	            'name' => (isset($_REQUEST['name'])?$_REQUEST['name']:''),
	            'phone' => (isset($_REQUEST['phone'])?$_REQUEST['phone']:''),
	            'uid' => $uid,
	            );
	            if(isset($_REQUEST['phone']))
	            {
	                $wh = array(
	            'phone' => (isset($_REQUEST['phone'])?$_REQUEST['phone']:''),
	            'uid' => $uid,
	            );
	            //check already
	            $al = $modal->get($wh);
	            if(!$al)
	            {
	            $r = $modal->add($in);
	            if($r)
	            {
	            $ret = array(
	        'status' => 1,
	        'msg'=> 'supplier add successfully!'
	        );      
	            }
	            }
	            else
	            {
	                $ret = array(
	        'status' => 0,
	        'msg'=> 'supplier already exist!'
	        );      
	            }
	            }
	    }
	    elseif($type == 'delete')
	    {
	        if(isset($_REQUEST['cid']))
	        {
	            $cid = $_REQUEST['cid'];
	            $cust = $modal->getbyid($cid);
	            if($cust->uid == $uid)
	            {
	                $modal->delete($cid);
	                $ret = array(
	        'status' => 1,
	        'msg'=> 'supplier delete successfully!'
	        );      
	            }
	            else
	            {
	                $ret = array(
	        'status' => 0,
	        'msg'=> 'Invalid supplier!'
	        );      
	            }
	        }
	    }
	    if($ret['status'])
	    {
	    $ret['user']=$this->get_user($uid,1);
	    }
		}
		
	    echo json_encode($ret);
	}
	public function customer($type)
	{
	    
	    $ret = array(
	        'status' => 0,
	        'msg'=> 'Invalid request'
	        );
	    $this->load->model('Common_model');

		$modal = $this->Common_model;
		$modal->table= 'customers';
		if(isset($_REQUEST['uid']))
		{
		    
		    $uid = $_REQUEST['uid'];
	    if($type == "add")
	    {
	        $in = array(
	            'name' => (isset($_REQUEST['name'])?$_REQUEST['name']:''),
	            'phone' => (isset($_REQUEST['phone'])?$_REQUEST['phone']:''),
	            'uid' => $uid,
	            );
	            if(isset($_REQUEST['phone']))
	            {
	                $wh = array(
	            'phone' => (isset($_REQUEST['phone'])?$_REQUEST['phone']:''),
	            'uid' => $uid,
	            );
	            //check already
	            $al = $modal->get($wh);
	            if(!$al)
	            {
	            $r = $modal->add($in);
	            if($r)
	            {
	            $ret = array(
	        'status' => 1,
	        'msg'=> 'Customer add successfully!'
	        );      
	            }
	            }
	            else
	            {
	                $ret = array(
	        'status' => 0,
	        'msg'=> 'Customer already exist!'
	        );      
	            }
	            }
	    }
	    elseif($type == 'delete')
	    {
	        if(isset($_REQUEST['cid']))
	        {
	            $cid = $_REQUEST['cid'];
	            $cust = $modal->getbyid($cid);
	            if($cust->uid == $uid)
	            {
	                $modal->delete($cid);
	                $ret = array(
	        'status' => 1,
	        'msg'=> 'Customer delete successfully!'
	        );      
	            }
	            else
	            {
	                $ret = array(
	        'status' => 0,
	        'msg'=> 'Invalid Customer!'
	        );      
	            }
	        }
	    }
	    if($ret['status'])
	    {
	    $ret['user']=$this->get_user($uid,1);
	    }
		}
		
	    echo json_encode($ret);
	}
	public function fatresponse($type, $order)

	{

		$this->load->model('Common_model');

		    $modal = $this->Common_model;

		    $data =array();

			$modal->table = 'order';

			$modal->key = 'id';

			$or = $modal->get(array('track'=> $order));

			$oid = 0;

			if($or)

			{

				$order = $or[0];

				$oid = $order['id'];
				$cid = $order['cid'];
				$total = $order['total'];

				$data['order'] = $order;

			}

			$up = array(

				'myfatoorah_response' => json_encode($_REQUEST)

			);

			if($type == 'success' )

			{
			    //$total
			    $user=$this->get_user($cid,1);
			    $nw = $user->wallet + $total;
			    
		$this->load->model('Common_model');

		        $modal = $this->Common_model;

		        $modal->key = 'UserID';

		        $modal->table = 'users';
		        $up = array('wallet'=>$nw);
		        $modal->update($cid, $up);
		        $modal->key = 'id';
		        

		        $modal->table = 'wallet_ledger';
		        $in =  array(
		         'dr' => $total, 
		         'reason' => "recharge",
		         'cID' => $cid, 
		         );
		         $r = $modal->add($in);
			    

				$data['status'] = 1;



				$up['pstatus'] = 1;

			}

			else

			{

				$data['status'] = 0;

				$up['pstatus'] = -1;

			}

			$modal->update($oid,$up);

			$this->load->library('template');

			$this->template->front('OrderSuccesful',$data);

	}

	public function vid_course($cid)

	{

	if($cid)

	{

		$this->load->model('Common_model');

		        $modal = $this->Common_model;

		        $modal->key = 'id';

		        $modal->table = 'module';

		        $module = $modal->get(array('cID'=> $cid));

		        foreach ($module as $key => $value) {

		        	?>

		        	<option value="<?= $value['id'] ?>"><?= $value['name'] ?></option>

		        	<?php

		        }

	}	

	}

	private function checkout_user($data)

	{ 



			if(isset($_SESSION['shiri_login']->UserID))

			return $_SESSION['shiri_login']->UserID;

	    $uid = 0;

	    $this->load->model('Common_model');

		        $modal = $this->Common_model;

		        $modal->key = 'UserID';

		        $modal->table = 'users';

		        //check email already exist

		        if(isset($data['email']))

		        {

					$user = $modal->get(array('email'=>$data['email'])); 

					if($user)

					{



						$user = $user[0];

						return $user['UserID'];

					}

					else{

						//insert

						$in = array();

						$in['roleID'] = 2;

						$pass= time();

						$in['upass'] = md5($pass);

						if(isset($data['uname']))

						{

							$in['first_name'] = $data['uname'];

						}

						if(isset($data['phone']))

						{

							$in['phone'] = $data['phone'];

						}

						if(isset($data['email']))

						{

							$in['email'] = $data['email'];

						}

						//bnew account creation email

						return $modal->add($in);

					}

		         

		        }

	    return $uid;

	}

	private function order_cart($oid)

	{

		if(isset($_SESSION['cart']))

		{

			$cart = $_SESSION['cart'];

			$this->load->model('Common_model');

		    $modal = $this->Common_model;

			$modal->table = 'order_item';

			$modal->key = 'id';

			foreach($cart as $k => $v)

			{

				$in = array(

						'pid' => $v['pid'],

						'qty' => ($v['qty'])?$v['qty']:1,

						'order_id' => $oid,

						'ptype' => (isset($v['ptype'])?$v['ptype']:""),

				);

				$r = $modal->add($in);

			}

			unset($_SESSION['cart']);

		}

	}

	private function cart_total()

	{

		$tot = 0;

		if(isset($_SESSION['cart']))

		{

			$cart = $_SESSION['cart'];

			$this->load->model('Common_model');

		    $modal = $this->Common_model;

			$modal->table = 'courses';

			$modal->key = 'id';

			foreach($cart as $k => $v)

			{

				$sing = $modal->getbyid($v['pid']);

                	        $tot = $tot + $sing->price;

			}

		}

		return $tot;

	}

	public function checkout()

	{ 

	    if(isset($_REQUEST['uid']) && isset($_REQUEST['amount']))
	    {
	        $uid = $_REQUEST['uid'];
	        $amount = $_REQUEST['amount'];
	        $user = $this->get_user($uid,1);

	    	$t = time();


            if($user)
            {
	        $uid = $user->UserID;
            }

			$in = array();

			$in['cid'] = $uid;

			$tot = $amount;

			$in['total'] = $tot;

			$in['pstatus'] = 0;

			$in['track'] = $t;

			$this->load->model('Common_model');

		    $modal = $this->Common_model;

			$modal->table = 'order';

			$modal->key = 'id';

			$oid = $modal->add($in);



	        	    $data1 = array (

      'InvoiceValue' => $tot,

      'CustomerName' => $user->first_name.''.$user->last_name,

      'CustomerBlock' => 'Block',

      'CustomerStreet' => 'Street',

      'CustomerHouseBuildingNo' => 'Building no',

      'CustomerCivilId' => '123456789124',

      'CustomerAddress' => 'Payment Address',

      'CustomerReference' => '\'.$t.\'',

      'DisplayCurrencyIsoAlpha' => 'KWD',

      'CountryCodeId' => '+965',

      'CustomerMobile' => $user->phone,

      'CustomerEmail' => $user->email,

      'DisplayCurrencyId' => 3,

      'SendInvoiceOption' => 1,

      'InvoiceItemsCreate' => 

      array (

        0 => 

        array (

          'ProductId' => NULL,

          'ProductName' => 'direct payment',

          'Quantity' => 1,

          'UnitPrice' => $tot,

        ),

  ),

  'CallBackUrl' => '',

  'Language' => 2,

  'ExpireDate' => '2022-12-31T13:30:17.812Z',

  'ApiCustomFileds' => 'weight=10,size=L,lenght=170',

  'ErrorUrl' => '',

);

if($data1 && isset($data1['InvoiceValue']) && $data1['InvoiceValue'] > 0 && $oid > 0)

{
	$domain = base_url();

    $surl = $domain.'api/fatresponse/success/'.$t;

    $eurl = $domain.'api/fatresponse/error/'.$t;

	$data1['CallBackUrl'] = $surl;

	$data1['ErrorUrl'] = $eurl;

	$this->load->library('myfathoorahv1');
    if(!$user->sandbox)
    {
		$this->myfathoorahv1->set_type('live');
    }

	$arr = $this->myfathoorahv1->genrate_link(json_encode($data1));

	if(isset($arr['RedirectUrl']))

	{

	    $ret = array(

	        'red'=> $arr['RedirectUrl']

	        );

	        echo json_encode($ret);

	        exit();

	}

}

	    }

	    else

	    {

	    	echo json_encode($ret);

	        exit();

	    }

	}

	public function add_cart()

	{

	    $cart = array();

	    $ret = array();

	    if(isset($_SESSION['cart']))

	    { 

	        $cart = $_SESSION['cart'];

	    }

	    if(isset($_REQUEST['pid']))

	    {

	        $pid = $_REQUEST['pid'];

	        $alre = 0;

	        foreach($cart as $k => $v)

	        {

	            if(!$alre && $pid == $v)

	            {

	                $alre = 1;

	            }

	        } 

	        //check already exit

	        if(!$alre)

	        {

	        	$ptype = (isset($_REQUEST['ptype'])?$_REQUEST['ptype']:"");

	            $cart[] = array(

	                'pid' =>$pid,

	                'ptype'=> $ptype

	                );

	                $_SESSION['cart'] = $cart;

	                $ret = array(

	                    'red' => base_url('index/page/checkout')

	                    );

	        }

	        else 

	        {

	            $ret = array(

	                    'msg' => 'Product already in cart'

	                    );

	        }

	        

	        

	    }

	    echo json_encode($ret);

	}

	public function send_coupon()

	{

		if(isset($_POST['coupon']) && isset($_POST['number']))

		{

		    if($_POST['type'] == 'sms')

		    {

		        $num = '965'.$_POST['number'];

		        $uid = $_POST['coupon'];

		        $liunk = base_url('index/coupon').'/'.$uid;

				$msg = 'Hello, your coupon link is '.$liunk.'. Thanks';

		        $mres = $this->send_sms($num, $msg);

			   $resp = json_decode($mres, true);

			   

					

			   if($resp['Result'] != "false")

			   {

					$ar = array(

						'status'=> 1,

						'msg'=> "Coupon send successfully!"

					);

					echo json_encode($ar);

					die();

				}

				else

				{

					// var_dump($resp);

			  //  die();

					$ar = array(

						'status'=> 0,

						'msg'=> $resp['Message']

					);

					echo json_encode($ar);

					die();

				}

		    }

		    elseif($_POST['type'] == 'email')

		    {

		        $mail = $_POST['number'];

		        $uid = $_POST['coupon'];

		        $liunk = base_url('index/coupon').'/'.$uid;

				$msg = 'Hello, your coupon link is '.$liunk.'. Thanks';

		        $mres = $this->send_mail($mail, $msg, 'Futer Kid Coupons');

		        $ar = array(

						'status'=> 1,

						'msg'=> "Coupon send successfully!"

					);

					echo json_encode($ar);

					die();

		        

		      //  die("send mail");

		    }

		    elseif($_POST['type'] == 'use')

		    {

		        $cid = $_POST['coupon'];

		        $this->load->model('Common_model');

		        $modal = $this->Common_model;

		        $modal->table = 'coupon_item';

			        $ret = $modal->update($cid, array('is_used'=>$_SESSION['knet_login']->UserID));

		        if($ret)

		        $ar = array(

						'status'=> 1,

						'msg'=> "Coupon used successfully!"

					);

					echo json_encode($ar);

					die();

		        

		      //  die("send mail");

		    }

			$this->load->model('Common_model');

			$cid = $_POST['coupon'];

			$num = $_POST['number'];

			$client_id = 0;

			//get client 

			$this->Common_model->table = 'clients';

			$modal = $this->Common_model;

			$user = $modal->get(array('phone'=>$num));

			if($user[0] && $user[0]['id'])

			{

			    $client_id = $user[0]['id'];

			    

			}

			else

			{

			    $user_id = $_SESSION['knet_login']->UserID;

			    $ar = array(

			        "phone"=>$num,

			        "userID"=>$user_id

			        );

			        $client_id = $modal->add($ar);

			}

			$this->Common_model->table = 'coupon_item';

			$modal = $this->Common_model;

			$user_id = $_SESSION['knet_login']->UserID;

			//insert new logic

			$modal->table = 'clients_to_user';

			$exist= $modal->get(array('userID'=>$user_id,'cID'=>$client_id));

			if(!$exist)

			{

			    $in = array('userID'=>$user_id,'cID'=>$client_id);

			    $modal->add($in);

			}

			//insert new logic

			

			

			$exp = explode(',', $cid);

			if($exp)

			{

			    $modal->table = 'coupon_item';

			    foreach($exp as $k => $v)

			    {

			        $modal->update($v, array('user'=>$client_id,'assign_by'=>$_SESSION['knet_login']->UserID));

			        

			    }

				$modal->table = 'clink';

				$code = time();

				$in = array(

					'code' => $code

				);

				// if($linkID = $modal->add($in))

				// {

				// 	$modal->table = 'link_to_coupon';

				// 	foreach ($exp as $key => $value) {

				// 		if($value)

				// 		{

				// 			$in = array(

				// 				'linkID'=> $linkID,

				// 				'cID'=> $value,

				// 			);

				// 			$modal->add($in);



				// 		}

				// 	}

				// 	$liunk = base_url('index/coupon').'/'.$code;

				// 	$msg = 'Hello, your coupon link is '.$liunk.'. Thanks';

				// }

			}

			$ar = array(

						'status'=> 1,

						'msg'=> "Coupon assigned successfully!"

					);

					echo json_encode($ar);

					die();

			

			$mres = $this->send_sms($num, $msg);

			   $resp = json_decode($mres, true);

			   

					

			   if($resp['Result'] != "false")

			   {

					$ar = array(

						'status'=> 1,

						'msg'=> "Coupon send successfully!"

					);

					echo json_encode($ar);

					die();

				}

				else

				{

					// var_dump($resp);

			  //  die();

					$ar = array(

						'status'=> 0,

						'msg'=> $resp['Message']

					);

					echo json_encode($ar);

					die();

				}

		}

	}

	 public function send_sms($num, $msg) {

	 	$code = mb_substr($num, 0, 3);

	 	$parms = [

   "username" => "future", 

   "password" => "Nazmul@123", 

   "customerId" => "316", 

   "senderText" => "Future Kid", 

   "recipientNumbers" => "", 

   "defdate" => "", 

   "isBlink" => "false", 

   "isFlash" => "false" 

];

    if( $code == "965"|| $code == '+96')

    {

// 		echo "Custom";

    }

    else{

        die("Twilio");

	}

	$num  = trim(str_replace("+"," ",$num));

    $parms['messageBody'] = $msg;

    $parms['recipientNumbers'] = $num;



$curl = curl_init();

$url = "http://sms.channelsmedia.com/SMSGateway/Services/Messaging.asmx/Http_SendSMS?".http_build_query($parms);

// die();



curl_setopt_array($curl, array(

  CURLOPT_URL => $url,

  CURLOPT_RETURNTRANSFER => true,

  CURLOPT_ENCODING => "",

  CURLOPT_MAXREDIRS => 10,

  CURLOPT_TIMEOUT => 0,

  CURLOPT_FOLLOWLOCATION => true,

  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,

  CURLOPT_CUSTOMREQUEST => "GET",

));



$response = curl_exec($curl);



curl_close($curl);

// return $response;

$fileContents = str_replace(array("\n", "\r", "\t"), '', $response);

$fileContents = trim(str_replace('"', "'", $fileContents));

$oldXml = $fileContents;

$simpleXml = simplexml_load_string($fileContents);

$json = json_encode($simpleXml);

return $json;

	 }

	 public function send_mail($to, $html, $sub) {



        $data = array (



              'personalizations' =>

              array (



                0 => 



                array (



                  'to' => 



                  array (



                    0 => 



                    array (



                      'email' => $to,



                    ),



                  ),



                  'subject' => $sub,



                ),



              ),



              'from' => 



              array (



                'email' => 'info@channelsmedia.com' ,

                'name' => 'channelsmedia',



              ),



              'content' => 



              array (



                0 => 



                array (



                  'type' => 'text/html',



                  'value' => $html,



                ),



              ),



            );



            



            $curl = curl_init();



            



              curl_setopt_array($curl, array(



              CURLOPT_URL => "https://api.sendgrid.com/v3/mail/send",



              CURLOPT_RETURNTRANSFER => true,



              CURLOPT_ENCODING => "",



              CURLOPT_MAXREDIRS => 10,



              CURLOPT_TIMEOUT => 30,



              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,



              CURLOPT_CUSTOMREQUEST => "POST",



              CURLOPT_POSTFIELDS => json_encode($data),



              CURLOPT_HTTPHEADER => array(



                "authorization: Bearer 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",



                "cache-control: no-cache",



                "content-type: application/json",



                "postman-token: 500e002d-9ecb-48a8-eb26-9e81cba79900"



              ),



            ));



            



            $response = curl_exec($curl);



            $err = curl_error($curl);



            



            curl_close($curl);

            if ($err) {



              echo $err;





            } else {



              $arr = json_decode($response);

              



            }



    }

	 public function rpass2()

	{

	header('Access-Control-Allow-Origin: *');

		if($_REQUEST && isset($_REQUEST['user_id']) && isset($_REQUEST['pass']) && $_REQUEST['pass'] && $_REQUEST['user_id'])

		{

			$id = $_REQUEST['user_id'];

			$pass = base64_encode($_REQUEST['pass']);

			$up = array('upass'=> $pass);

			$user = $this->db->where('id',$id)->get('clients')->row();

			

			$rdata = array();

				if($user)

				{

				    unset($user->upass);

				    unset($user->token);

					unset($user->app_type);

				    $res = $this->db->where('id',$id)->update('clients', $up);

				    $html = 'your password updated';

				   $this->send_mail($user->email, $html, 'Password Update'); 

					$rdata = array(

						"status" => 1,

						"msg" => 'update successfully',

						"data" => $user,

					);

				}

				else

				{

					$rdata = array(

						"status" => 0,

						"text" => "No Users Found"

					);

				}

				echo json_encode($rdata);

		}

		else{

			die("invalid request");

		}

	}

	 public function rpass1()

	{

	header('Access-Control-Allow-Origin: *');

		if($_REQUEST && isset($_REQUEST['email']) && $_REQUEST['email'])

		{

			$email = $_REQUEST['email'];

			$digits = 4;

			$code = rand(pow(10, $digits-1), pow(10, $digits)-1);

			$up = array('rcode', $code);

			$res = $this->db->where('email',$email)->get('clients')->row();

			$rdata = array();

				if($res)

				{

				    $html = 'your code is '.$code;

				   $this->send_mail($email, $html, 'Rest Password code'); 

					$res->code = $code;

					$rdata = array(

						"status" => 1,

						"data" => $res,

					);

				}

				else

				{

					$rdata = array(

						"status" => 0,

						"text" => "No Users Found"

					);

				}

				echo json_encode($rdata);

		}

		else{

			die("invalid request");

		}

	}

	public function updateProfile()

	{

		$rdata = array();

		if($_REQUEST && isset($_REQUEST['user_id']))

		{

			$id = $_REQUEST['user_id'];

			$user = $this->db->where('id',$id)->get('clients')->row();

			if($user)

			{

			$this->load->model('Common_model');

					$this->Common_model->table = 'clients';

					$modal = $this->Common_model;



			$id = $_REQUEST['user_id'];

			if(isset($_REQUEST['fname']) && !empty($_REQUEST['fname']))

			{

					$ret = $modal->update($id, array('fname'=>$_REQUEST['fname']));

			}

			if(isset($_REQUEST['lname']) && !empty($_REQUEST['lname']))

			{

					$modal->update($id, array('lname'=>$_REQUEST['lname']));

			}

			if(isset($_REQUEST['gender']) && !empty($_REQUEST['gender']))

			{

					$modal->update($id, array('gender'=>$_REQUEST['gender']));

			}

			if(isset($_REQUEST['age']) && !empty($_REQUEST['age']))

			{

					$modal->update($id, array('age'=>$_REQUEST['age']));

			}

			if(isset($_REQUEST['height']) && !empty($_REQUEST['height']))

			{

					$modal->update($id, array('height'=>$_REQUEST['height']));

			}

			if(isset($_REQUEST['weight']) && !empty($_REQUEST['weight']))

			{

					$modal->update($id, array('weight'=>$_REQUEST['weight']));

			}

					$this->load->model('Common_model');

					$this->Common_model->table = 'clients';

					$modal = $this->Common_model;

				    $media = $modal->getMediaByID($user->mediaID);

		        if($media)

		        {

		        	$user->img = base_url().$media->localPath;

		        }

		        else

		        {

		        	$user->img = $this->dummyimage();

		        }

					unset($user->upass);

					unset($user->token);

					unset($user->app_type);

			$rdata['status'] = 1;

			$rdata['msg'] = 'Profile update successfully!';

		}

		else

		{

			$rdata['status'] = 0;

			$rdata['msg'] = 'User does not exist!';

		}

		}

		else{

			$rdata['status'] = 0;

			$rdata['msg'] = 'invalid request!';



		}	

		echo json_encode($rdata)	;

	}

	public function signup_main()

	{

header('Access-Control-Allow-Origin: *');



		if($_REQUEST['request'])
		{
		    
			$request = json_decode($_REQUEST['request'],true);
			$user = array();
			if($request)
			{
				$user = $request;
			}
			





			$indata= array(

				"email" =>$user['user_email'],
				"main_id" =>(isset($user['ID'])?$user['ID']:0),
				"magento" =>(isset($user['magento'])?$user['magento']:0),
				"phone" =>$user['billing_phone'],
				"phone" =>$user['billing_phone'],

				"upass" =>md5($user['user_pass']),

				"first_name" =>$user['user_login'],

				"last_name" =>'',
				"roleID" =>($user['role'] == 2298)?4:3,

			);
			

			$res = $this->db->where('email',$user['user_email'])->get('users')->row();

			if($res)

			{

			    $rdata = array(

						"status" => 0,

						"text" => "Users Already exist."

					);

	 				echo json_encode($rdata);

					exit();

			}

			$res = $this->db->where('phone',$user['billing_phone'])->get('users')->row();

			if($res)

			{

			    $rdata = array(

						"status" => 0,

						"text" => "phone Already exist."

					);

	 				echo json_encode($rdata);

					exit();

			}

			$rdata = array();

				if($id = $this->db->insert('users',$indata))

				{
				    $id = $this->db->insert_id();

				

					

					$rdata = array(

						"status" => 1,

						"data" => $this->get_user($id,1),

						"text" => "Account creat successfully!"

					);

				}

				else

				{

					$rdata = array(

						"status" => 0,

						"text" => "server error"

					);

				}

				echo json_encode($rdata);

		}

		else{

			die("invalid request");

		}

	}
	public function signup()

	{

header('Access-Control-Allow-Origin: *');

		if($_REQUEST)

		{




			$indata= array(

				"email" =>$_REQUEST['email'],

				"upass" =>md5($_REQUEST['pass']),

				"first_name" =>$_REQUEST['name'],
				"phone" =>$_REQUEST['phone'],
				"roleID" =>$_REQUEST['roleID'],
				"com_licen" =>(isset($_REQUEST['com_licen'])?$_REQUEST['com_licen']:0),
				"sign_doc" =>(isset($_REQUEST['sign_doc'])?$_REQUEST['sign_doc']:0),
				"civil_id" =>(isset($_REQUEST['civil_id'])?$_REQUEST['civil_id']:0),
				"owner_id" =>(isset($_REQUEST['owner_id'])?$_REQUEST['owner_id']:''),
				"comp_name" =>(isset($_REQUEST['comp_name'])?$_REQUEST['comp_name']:'')

			);
			//die("OK");http://localhost/bak_pay/api/signup?email=raheel@test.com&fname=Raheel Shehzad&pass=admin&=4

			$res = $this->db->where('email',$_REQUEST['email'])->get('users')->row();

			if($res)

			{

			    $rdata = array(

						"status" => 0,

						"text" => "Users Already exist."

					);

	 				echo json_encode($rdata);

					exit();

			}

			$res = $this->db->where('phone',$_REQUEST['phone'])->get('users')->row();

			if($res)

			{

			    $rdata = array(

						"status" => 0,

						"text" => "phone Already exist."

					);

	 				echo json_encode($rdata);

					exit();

			}

			$rdata = array();

				if($id = $this->db->insert('users',$indata))

				{
					$id = $this->db->insert_id();

					$user = $this->db->where('UserID',$id)->get('users')->row();

					$this->load->model('Common_model');

					$this->Common_model->table = 'users';
					$this->Common_model->key = 'UserID';

					$modal = $this->Common_model;

				    $media = $modal->getMediaByID($user->mediaID);

		        if($media)

		        {

		        	$user->img = base_url().$media->localPath;

		        }

		        else

		        {

		        	$user->img = $this->dummyimage();

		        }

					unset($user->upass);

					unset($user->token);

					unset($user->app_type);

					

					$rdata = array(

						"status" => 1,

						"data" => $user,

						"text" => "Account creat successfully!"

					);

				}

				else

				{

					$rdata = array(

						"status" => 0,

						"text" => "server error"

					);

				}

				echo json_encode($rdata);

		}

		else{

			die("invalid request");

		}

	}

	public function updateProfileImg()

	{

		header('Access-Control-Allow-Origin: *');

		if(isset($_FILES['img']) && isset($_REQUEST['user_id']))

		{

			$this->load->library('template');

			$mediaID = 0;

        	if(isset($_FILES['img']['name']) && !empty($_FILES['img']['name']))

	        {

	        	

	        	$imgData = $this->template->upload('img');

	        	if($imgData['localPath'])

	        	{

	        		$this->load->model('Common_model');

				$this->Common_model->table = 'clients';

				$modal = $this->Common_model;



	        		$mediaID = $modal->addMedia($imgData);



	        	}

	        }

	        if($mediaID)

	        {

	        	$rdata = array();

	        	$up = array(

	        		'mediaID'=> $mediaID

	        	);

				if($res = $this->db->where('id',$_REQUEST['user_id'])->update('clients', $up))

				{

					

					$rdata = array(

						"status" => 1,

						"text" => "Account update successfully!"

					);

				}

				else

				{

					$rdata = array(

						"status" => 0,

						"text" => "server error"

					);

				}

				echo json_encode($rdata);

	        }

		}

		else{

			die("invalid request");

		}

	}

	public function upload()

	{

		header('Access-Control-Allow-Origin: *');


		if(isset($_FILES['file']))

		{


			$this->load->library('template');

			$mediaID = 0;

        	if(isset($_FILES['file']['name']) && !empty($_FILES['file']['name']))

	        {

	        	

	        	$imgData = $this->template->upload('file');

	        	if($imgData['localPath'])

	        	{

	        		$this->load->model('Common_model');

				$this->Common_model->table = 'clients';

				$modal = $this->Common_model;



	        		$mediaID = $modal->addMedia($imgData);



	        	}

	        }

	        if($mediaID)
	        {
	        	$ret = $modal->getMediaByID($mediaID);
	        	if($ret)
				{

					
						$rdata = array(
	        			'attach_id'=> $ret->mediaID,
	        			"status" => 1,
	        			'url'=> base_url('/').$ret->localPath,
	        		);

				}
				else
				{

					$rdata = array(

						"status" => 0,

						"text" => "server error"

					);

				}

				echo json_encode($rdata);

	        }

		}

		else{

			die("invalid request");

		}

	}

	public function applogin()

	{

	header('Access-Control-Allow-Origin: *');

		if($_REQUEST)

		{

			$email = $_REQUEST['email'];

			$pass = $_REQUEST['pass'];

			$pass = md5($pass);

			$user = $this->db->where('phone',$email)->where('upass',$pass)->get('users')->row();
			$rdata = array();
				if($user)
				{

				    if(isset($_REQUEST['token']) && isset($_REQUEST['app_type']))

				    {

    				    $token = $_REQUEST['token'];

    			        $app_type = $_REQUEST['app_type'];

    			        $up = array(

    			            "app_type" => $app_type,

    			            "token" => $token,

    			            );

    			            $res = $this->db->where('UserID',$user->UserID)->update('users', $up);

				    }

				    $this->load->model('Common_model');

					$this->Common_model->table = 'clients';

					$modal = $this->Common_model;

				    $media = $modal->getMediaByID($user->mediaID);

		        if($media)

		        {

		        	$user->img = base_url().$media->localPath;

		        }

		        else

		        {

		        	$user->img = $this->dummyimage();

		        }

					unset($user->upass);

					unset($user->token);

					unset($user->app_type);

					$rdata = array(

						"status" => 1,

						"data" => $user,

						"text" => "Login successfully!"

					);

				}

				else

				{

					$rdata = array(

						"status" => 0,

						"text" => "Invalid credentials"

					);

				}

				echo json_encode($rdata);

		}

		else{

			die("invalid request");

		}

	}

	

	public function artist()

	{

	header('Access-Control-Allow-Origin: *');

	

	    $this->load->model('Common_model');

		$this->Common_model->table = 'artist';

		$modal = $this->Common_model;

		$Where = $_GET;

		$Where['status'] = 0;

		$data= $modal->get($Where);

// 		die('OK');

		$rdata = array();

		

		if($data)

		   { 

		       

		    $ret =  array();

		    foreach($data as $key=>$val)

		    {

		        $sing = array();

		        $sing['id'] = $val['id'];

		        $sing['name'] = $val['name'];

		        $media = $modal->getMediaByID($val['mediaID']);

		        if($media)

		        $sing['img'] = base_url().$media->localPath;

		        $video = $modal->getMediaByID($val['videoID']);

		        if($video)

		        $sing['video'] = base_url().$video->localPath;

		        

		        $ret[] = $sing;

		    }

		    $rdata = array(

						"status" => 1,

						"data" => $ret

					);

					echo json_encode($rdata);

					return 0;

		    }

		    else

			{

				$rdata = array(

					"status" => 0,

					"text" => "No record found!"

				);

				echo json_encode($rdata);

					return 0;

			}

			

			

	

	}

	public function music()

	{

	header('Access-Control-Allow-Origin: *');

	

	    $this->load->model('Common_model');

		$this->Common_model->table = 'music';

		$modal = $this->Common_model;

		$Where = $_GET;

		$Where['status'] = 0;

		$data= $modal->get($Where);

// 		die('OK');

		$rdata = array();

		

		if($data)

		   { 

		       

		    $ret =  array();

		    foreach($data as $key=>$val)

		    {

		        $sing = array();

		        $sing['id'] = $val['id'];

		        $sing['title'] = $val['title'];

		        $media = $modal->getMediaByID($val['mediaID']);

		        if($media)

		        $sing['img'] = base_url().$media->localPath;

		        $video = $modal->getMediaByID($val['audioID']);

		        if($video)

		        $sing['audio'] = base_url().$video->localPath;

		        

		        $cat = array();

		        if($val['artistID'])

		        {

		        $modal->table = 'artist';

                                        $cat =  $modal->getbyid($val['artistID']);

                                        $sing ['artist'] = $cat;

		        }

		        if($val['albumID'])

		        {

		        $modal->table = 'albums';

                                        $cat =  $modal->getbyid($val['albumID']);

                                        $sing ['album'] = $cat;

		        }

		        

		        $ret[] = $sing;

		    }

		    $rdata = array(

						"status" => 1,

						"data" => $ret

					);

					echo json_encode($rdata);

					return 0;

		    }

		    else

			{

				$rdata = array(

					"status" => 0,

					"text" => "No record found!"

				);

				echo json_encode($rdata);

					return 0;

			}

			

			

	

	}

	public function workouts()

	{

		// die("OK");

	header('Access-Control-Allow-Origin: *');

	

	    $this->load->model('Common_model');

		$this->Common_model->table = 'videos';

		$modal = $this->Common_model;

		$Where = $_GET;

		$Where['status'] = 0;

		if(isset($_GET['trainerID']))

		{$sql = 'SELECT * FROM `videos` WHERE FIND_IN_SET('.$_GET['trainerID'].', trainerID) ';

		    $data= $this->db->query($sql)->result_array();

		}

		else

		{

		 $data= $modal->get($Where);   

		}

		$rdata = array();

		

		if(count($data) > 0)

	   { 

		       

		    $ret =  array();

		    foreach($data as $key=>$val)

		    {

		        $sing = array();

		        $sing['id'] = $val['id'];

		        $sing['name'] = $val['title'];

		        $sing['discription'] = $val['discription'];

		        $sing['calories'] = $val['calories'];

		        $sing['equipment'] = $val['equipment'];

		        $media = $modal->getMediaByID($val['mediaID']);

		        if($media)

		        {

		        	$sing['img'] = base_url().$media->localPath;

		        }

		        else

		        {

		        	$sing['img'] = $this->dummyimage();

		        }

		        // $video = $modal->getMediaByID($val['videoID']);

		        if($val['videoID'])

		        {

		        	$url = 'https://player.vimeo.com/video/'.$val['videoID'].'/config';

		        	$content = file_get_contents($url);

		        	$ar = json_decode($content, true);

		        	$vid = '';

		        	if(isset($ar['request']['files']['progressive']))

		        	$video_ar = $ar['request']['files']['progressive'];

		        	foreach ($video_ar as $key => $value) {

		        		if($value['quality'] == '540p')

		        			$vid = $value['url'];

		        	}

		        	$sing['video'] = $vid;

		        }

		        else

		        {

		        	$sing['video'] = '';

		        }

		        //trainers

		        // $trauiners = array();

		        // if($val['trainerID'])

		        // {

			       //  $modal->table = 'trainer';

	         //        $arr = explode(',', $val['trainerID']);

	         //        foreach ($arr as $key => $sing1) 

	         //        {

	         //            $trauiners[] =  $modal->getbyid($sing1);

	         //        }

          //       	$sing['trainers'] = $trauiners;

		        // }

		         $trauiners = array();



	        if($val['trainerID'])

	        {

		        $modal->table = 'trainer';

	            $arr = explode(',', $val['trainerID']);

	            

	            foreach ($arr as $key => $sing1) 

	            {

	                $arradata = $modal->getbyid($sing1);

	                $trauiners[] =  $arradata->name;

	            }

	            

        	}



        	$sing['trainers'] = implode(' , ', $trauiners);

		        $cat = array();

		        if($val['catID'])

		        {

		        	$modal->table = 'genres';

                    $cat =  $modal->getbyid($val['catID']);

                    $sing ['cat'] = $cat;

		        }

		        

		        $ret[] = $sing;

		    }

		    $rdata = array(

				"status" => 1,

				"data" => $ret

			);

			echo json_encode($rdata);

			return 0;

	    }

	    else

		{

			$rdata = array(

				"status" => 0,

				"text" => "No record found!"

			);

			echo json_encode($rdata);

			return 0;

		}

	}

	public function albums()

	{

	header('Access-Control-Allow-Origin: *');

	

	    $this->load->model('Common_model');

		$this->Common_model->table = 'albums';

		$modal = $this->Common_model;

		

		$data= $modal->get(array('status'=> 0));

// 		die('OK');

		$rdata = array();

		

		if($data)

		   { 

		       

		    $ret =  array();

		    foreach($data as $key=>$val)

		    {

		        $sing = array();

		        $sing['id'] = $val['id'];

		        $sing['name'] = $val['name'];

		        $media = $modal->getMediaByID($val['mediaID']);

		        if($media)

		        $sing['img'] = base_url().$media->localPath;

		        $ret[] = $sing;

		    }

		    $rdata = array(

						"status" => 1,

						"data" => $ret

					);

					echo json_encode($rdata);

					return 0;

		    }

		    else

			{

				$rdata = array(

					"status" => 0,

					"text" => "No record found!"

				);

				echo json_encode($rdata);

					return 0;

			}

			

			

	

	}

	public function trainers()

	{

	header('Access-Control-Allow-Origin: *');

	

	    $this->load->model('Common_model');

		$this->Common_model->table = 'trainer';

		$modal = $this->Common_model;

		

		$data= $modal->get(array('status'=> 0));

// 		die('OK');

		$rdata = array();

		

		if($data)

		   { 

		       

		    $ret =  array();

		    foreach($data as $key=>$val)

		    {

		        $sing = array();

		        $sing['id'] = $val['id'];

		        $sing['name'] = $val['name'];

		        $media = $modal->getMediaByID($val['mediaID']);

		        $media = $modal->getMediaByID($val['mediaID']);

		        if($media)

		        {

		        	$sing['img'] = base_url().$media->localPath;

		        }

		        else

		        {

		        	$sing['img'] = $this->dummyimage();

		        }

		        $ret[] = $sing;

		    }

		    $rdata = array(

						"status" => 1,

						"data" => $ret

					);

					echo json_encode($rdata);

					return 0;

		    }

		    else

			{

				$rdata = array(

					"status" => 0,

					"text" => "No record found!"

				);

				echo json_encode($rdata);

					return 0;

			}

			

			

	

	}

	public function workout_categories()

	{

	header('Access-Control-Allow-Origin: *');

	

	    $this->load->model('Common_model');

		$this->Common_model->table = 'genres';

		$modal = $this->Common_model;

		

		$data= $modal->get(array('status'=> 0));

// 		die('OK');

		$rdata = array();

		

		if($data)

		   { 

		       

		    $ret =  array();

		    foreach($data as $key=>$val)

		    {

		        $sing = array();

		        $sing['id'] = $val['id'];

		        $sing['name'] = $val['name'];

		        $media = $modal->getMediaByID($val['mediaID']);

		        if($media)

		        {

		        	$sing['img'] = base_url().$media->localPath;

		        }

		        else

		        {

		        	$sing['img'] = $this->dummyimage();

		        }

		        $ret[] = $sing;

		    }

		    $rdata = array(

						"status" => 1,

						"data" => $ret

					);

					echo json_encode($rdata);

					return 0;

		    }

		    else

			{

				$rdata = array(

					"status" => 0,

					"text" => "No record found!"

				);

				echo json_encode($rdata);

					return 0;

			}

			

			

	

	}



	public function homepage()

	{

		header('Access-Control-Allow-Origin: *');

		$this->load->model('Common_model');

		$this->Common_model->table = 'genres';

		$modal = $this->Common_model;

		$data_cate = $modal->get(array('status'=> 0),6);



		$total_cate = array();

		foreach($data_cate as $key=>$val)

	    {

	        $sing = array();

	        $sing['id'] = $val['id'];

	        $sing['name'] = $val['name'];

	        $media = $modal->getMediaByID($val['mediaID']);

	        if($media)

	        {

	        	$sing['img'] = base_url().$media->localPath;		

	        }

	        else

	        {

	        	$sing['img'] = $this->dummyimage();

	        }

	        $total_cate[] = $sing;

	    }





		$this->Common_model->table = 'trainer';

		$modal = $this->Common_model;

		$data_trrainer = $modal->get(array('status'=> 0));



		$total_trainer = array();

		foreach($data_trrainer as $key=>$val)

	    {

	        $sing = array();

	        $sing['id'] = $val['id'];

	        $sing['name'] = $val['name'];

	        $media = $modal->getMediaByID($val['mediaID']);

	        if($media)

	        {

	        	$sing['img'] = base_url().$media->localPath;		

	        }

	        else

	        {

	        	$sing['img'] = $this->dummyimage();

	        }

	        $total_trainer[] = $sing;

	    }



		$this->Common_model->table = 'videos';

		$modal = $this->Common_model;

		$data_videos = $modal->get(array('status'=> 0),4);

		$total_workout = array();

		foreach($data_videos as $key=>$val)

	    {

	        $sing = array();

	        $sing['id'] = $val['id'];

	        $sing['name'] = $val['title'];

	        $sing['disc'] = $val['discription'];

	        $sing['calories'] = $val['calories'];

	        $sing['equipment'] = $val['equipment'];

	        $media = $modal->getMediaByID($val['mediaID']);



	        if($media)

	        {

	        	$sing['img'] = base_url().$media->localPath;		

	        }

	        else

	        {

	        	$sing['img'] = $this->dummyimage();

	        }



	        $trauiners = array();



	        if($val['trainerID'])

	        {

		        $modal->table = 'trainer';

	            $arr = explode(',', $val['trainerID']);

	            

	            foreach ($arr as $key => $sing1) 

	            {

	                $arradata = $modal->getbyid($sing1);

	                $trauiners[] =  $arradata->name;

	            }

	            

        	}



        	$sing['trainers'] = implode(' , ', $trauiners);



	       if($val['videoID'])

		        {

		        	$url = 'https://player.vimeo.com/video/'.$val['videoID'].'/config';

		        	$content = file_get_contents($url);

		        	$ar = json_decode($content, true);

		        	$vid = '';

		        	if(isset($ar['request']['files']['progressive']))

		        	$video_ar = $ar['request']['files']['progressive'];

		        	foreach ($video_ar as $key => $value) {

		        		if($value['quality'] == '540p')

		        			$vid = $value['url'];

		        	}

		        	$sing['video'] = $vid;

		        }



	        $total_workout[] = $sing;

	    }



	    $return = array(

	    	'category'=>$total_cate,

	    	'trainer'=>$total_trainer,

	    	'workout'=>$total_workout

	    );

	    $rdata = array(

			"status" => 1,

			"data" => $return

		);

		echo json_encode($rdata);

		return 0;

	}



	public function dummyimage()

	{

		return 'https://dummyimage.com/600x400/000/fff';

	}



	public function index()

	{

		die();

		$assets = base_url('assets/admin');

		

		$data['assets'] = $assets;

		$this->load->view('main', $data);

	}

	public function addroute()

	{

		$assets = base_url('assets/admin');

		

		$data['assets'] = $assets;

		$data['page'] = "addroute";

		

		$this->load->view('main', $data);

	}

	public function addcity()

	{

		if($_POST)

		{

			$this->db->insert('cities',$_POST);

		}

		$assets = base_url('assets/admin');

		

		$data['assets'] = $assets;

		$data['page'] = "addcity";

		

		$this->load->view('main', $data);

	}

	public function dashboard()

	{

		$assets = base_url('assets/admin');

		$clients= $this->db->get('clients')->result_array();

		

		$data['clients'] = count($clients);

		$data['assets'] = $assets;

		$data['page'] = "dashboard";

		

		$this->load->view('main', $data);

	}

	public function login()

	{

		$assets = base_url('assets/admin');

		

		$data['assets'] = $assets;

		$this->load->view('login', $data);

	}



	public function single()

	{

		header('Access-Control-Allow-Origin: *');

		$return  =  array(

			'img'=>'https://i.stack.imgur.com/ddX9U.png"/',

			'rname'=>'Lahore to Khushab',

			'disc'=>'The most popular industrial group ever, and largely

      responsible for bringing the music to a mass audience.',

			'driver'=>'azeem ali',

			'distance'=>'102KM',

			'time'=>'11am - 3am'

		);

		$new = array(

			'data'=>$return,

			'status' => 1

		);

		echo json_encode($new);

	}

}


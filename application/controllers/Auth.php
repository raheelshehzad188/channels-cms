<?php
ob_start();
defined('BASEPATH') OR exit('No direct script access allowed');
class Auth extends CI_Controller {
	/**
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
	public function verify()
    {
        $status = 0;
        if(isset($_GET['token']))
        {
            $this->load->model('auth_model');
            $token = $_GET['token'];
            $user = $this->auth_model->get(array('token'=>md5($token),'roleID'=>2,'email_status'=>0));
            if(isset($user[0]))
            {
                $user = $user[0];
                $userID = $user['UserID'];
                $this->auth_model->updateuserbyid($userID, array("email_status"=>1,'token'=>' '));
                $status = 2;
            }
            else
            {
                $status = 1;
            }

        }else{
            $this->load->model('auth_model');
            $token = $_POST['token'];
            $email = $_POST['email'];
            $user = $this->auth_model->get(array('token'=>$token,'roleID'=>2,'email_status'=>0,'email'=>$email,));
            if(isset($user[0]))
            {
                $user = $user[0];
                $userID = $user['UserID'];
                $this->auth_model->updateuserbyid($userID, array("email_status"=>1,'token'=>' '));
                $status = 2;
            }
            else
            {
                $this->session->unset_userdata('veremail');
                $status = 1;
            }            
        }
        if($status==1){
        $this->session->set_flashdata('success', 'Your account is successfully varified.');
        redirect(base_url().'auth/login');
        }
        $this->session->set_flashdata('warning', 'Please try again...');
        redirect(base_url().'auth/verification');
        exit;    
         
    }
    public function create()
	{
		//$this->form_validation->set_rules('uname', 'User Name', 'required');
// 		$this->form_validation->set_rules('first_name', 'First Name', 'required');
// 		$this->form_validation->set_rules('last_name', 'Last Name', 'required');
// 		$this->form_validation->set_rules('last_name', 'Last Name', 'required');
		$this->form_validation->set_rules('email', 'Email', 'required');
// 		$this->form_validation->set_rules('cemail', 'Confirm Email', 'required');
        $this->form_validation->set_rules('upass', 'Password', 'required');
        $this->form_validation->set_rules('cpass', 'Confirm Password', 'required');
        if ($this->form_validation->run() == FALSE)
        {
            $this->session->set_flashdata('error', validation_errors());
        }
        else
        {
        	$this->load->model('auth_model');
        	if($this->input->post('upass') != $this->input->post('cpass'))
        	{
        		$this->session->set_flashdata('error', "Password Not Match!");
        		header('Location: ' . $_SERVER['HTTP_REFERER']);
        	}
        	if($this->auth_model->get(array('uname'=>$this->input->post('email'))))
        	{
        		$this->session->set_flashdata('error', "User Name Not Avalible");
        		header('Location: ' . $_SERVER['HTTP_REFERER']);
        		exit();
        	}
        	if($this->auth_model->get(array('email'=>$this->input->post('email'))))
        	{
        		$this->session->set_flashdata('error', "Email Already Exist");
        		header('Location: ' . $_SERVER['HTTP_REFERER']);
        		exit();
        	}
            $digits = 4;
            $token = rand(1000000,100000);

        	$arr = array(
        		"uname"=>$this->input->post('email'),
        		"first_name"=>'',
        		"last_name"=>'',
        		"email"=>$this->input->post('email'),
        		"upass"=>md5($this->input->post('upass')),
        		"roleID"=>2,
        		"ip"=>$this->input->ip_address(),
                "token"=>md5($token),
        	);
        	if($bookID = $this->auth_model->create($arr))
        	{
        		$em = array(
                    "name"=> $arr['first_name'].' '.$arr['last_name'],
                    "link"=> base_url('/auth/verify').'?token='.$token,
                    "token"=>$token,
                );
                $msg = $this->load->view('mail/signup', $em, true);
                $email = $this->input->post('email');
                $this->session->set_userdata('veremail',$email);
                $this->template->mail($email,$msg, "Email Activation!");
                $this->session->set_flashdata('success', 'Account Created successfully! Please check your email to verify code');
                
        	}
        	else
        	{
        		$this->session->set_flashdata('error', $this->db->_error_message());
        		
        	}
        }
        redirect($_SERVER['HTTP_REFERER']);
	}
	public function verification()
    {
        $data= array();
        $data['assets']= base_url('assets/books/');
        $data['email']=$this->session->userdata('veremail');
        // die($data['assets']);
        $this->load->library('template');
        $this->template->front('codeverification',$data);
    }
    public function register()
	{
		$data= array();
		$data['assets']= base_url('assets/books/');
		// die($data['assets']);
		$this->load->library('template');
		$this->template->front('register',$data);
	}
	public function post()
	{

		$this->form_validation->set_rules('uname', 'Username', 'required');
        
        $this->form_validation->set_rules('upass', 'Password', 'required');

        if ($this->form_validation->run() == FALSE)
        {
        	$this->session->set_flashdata('error', 'All fields required');
		}
        else
        { 
        	$uname = $this->input->post('uname');
        	$upass = $this->input->post('upass');
        	$this->load->model('auth_model');
        	$user = $this->auth_model->login($uname, $upass);
            if(!$user)
            {
                $this->session->set_flashdata('error', 'incorrect username or password !');    
        	}
            elseif($user->status == 0)
        	{
        		unset($user->upass);
            	$roleID = $user->roleID;
                if($roleID)
                {


            	$role = $this->auth_model->getrolebyid($roleID);
                }
                else{
                        $this->session->set_flashdata('error', 'system error');
                        redirect($_SERVER['HTTP_REFERER']);
                }
            	$lgn = array();
            	$lgn[$role->name.'_login'] = $user;
            	$this->auth_model->updateuserbyid($user->userID, array("ip"=>$this->input->ip_address()));
            	$_SESSION[$role->name.'_login'] = $user;
                $_SESSION['shiri_login'] = $user;
           		redirect(base_url(), 'refresh');
                exit();
            }
            else
            {
            	$this->session->set_flashdata('error', 'Account Blocked!');
            }
        }
        redirect($_SERVER['HTTP_REFERER']);
	}
	public function logout()
    {
        session_destroy();
	    unset($_SESSION['user_login']);
	    redirect(base_url());
    }
    public function login()
	{
                
		if(isset($_SESSION['user_login']))
		{
		        redirect(base_url().'books');
		}
		$data= array();
		$data['assets']= base_url('assets/books/');
		// die($data['assets']);
		$this->load->library('template');
		$this->template->front('login',$data);

		
	}
    public function setPassword()
    {
        if(isset($_SESSION['user_login']))
        {
            redirect(base_url().'books');
        }
        if($_POST)
        {
            $this->load->model('auth_model');
            $mail=$this->input->post('email');
            $newpassword=$this->input->post('upassword');
            $confirmpassword=$this->input->post('conpassword');
            $userCheck=$this->auth_model->get(['email'=>$mail]);
            $userCheck=$userCheck[0];
            $code=$userCheck['code'];
            if($newpassword==$confirmpassword)
            {
               $userid=$userCheck['UserID']; 
              if($this->auth_model->updatePassword($userid,$newpassword)){
              $this->session->set_flashdata('success', 'change password successfully!');
              redirect(base_url().'Auth/login','refresh');
              }else{  
              $this->session->set_flashdata('warning', 'confirm password is wrong!');
              }  

            }else{
              $this->session->set_flashdata('warning', 'Incorrect confirm password!');  
            }    
                

        }
        redirect(base_url().'Auth/Confirm/'.$code);
    }
    public function changePassword()
    {
        if(!isset($_SESSION['user_login']))
        {
            redirect(base_url().'books');
        }
        if($_POST)
        {
            $this->load->model('auth_model');
            $password=$this->input->post('old_password');
            $newpassword=$this->input->post('new_password');
            $confirmpassword=$this->input->post('confirm_password');
            $user=$_SESSION['user_login'];
            $userChek=$this->auth_model->login($user->email, $password);
            if($userChek)
            {
                if($newpassword==$confirmpassword)
                {
                  if($this->auth_model->updatePassword($userChek->UserID,$newpassword))
                  $this->session->set_flashdata('success', 'change password successfully!');
                  else  
                  $this->session->set_flashdata('warning', 'confirm password is wrong!');  

                }else{
                  $this->session->set_flashdata('warning', 'confirm password is wrong!');  
                }    
            }
            else
            {
                 $this->session->set_flashdata('warning', 'your password is wrong!');
            }    

        }
        redirect($_SERVER['HTTP_REFERER']);
    }
    public function reset()
    {
        $data= array();
        $data['assets']= base_url('assets/books/');
        
        if(isset($_SESSION['user_login']))
        {
           redirect(base_url().'books');
        }
        
        $data= array();
    	$data['assets']= base_url('assets/books/');
    	// die($data['assets']);
    	$this->load->library('template');
    	$this->template->front('fpass',$data);
    }
    public function confirmCode()
    {
        $data= array();
        $data['assets']= base_url('assets/books/');
        if(isset($_SESSION['user_login']))
        {
           redirect(base_url().'books');
        }
        $data= array();
        $data['assets']= base_url('assets/books/');
        // die($data['assets']);
        $this->load->library('template');
        $this->template->front('changePasswordCode',$data);
    }
    public function Confirm($code=null)
    {
        if(isset($_SESSION['user_login']))
        {
                redirect(base_url().'books');
        }
        $this->load->model('auth_model');
        if($_POST)
        {
            // $email = $this->session->userdata('checkMail');
            // echo  $email; die();
            $code=$this->input->post('token');
        }    
        $user = $this->auth_model->get(array('code'=>$code));
        if($user[0])
        {
            
            $user = $user[0];
            $UserID   = $user['UserID'];
            $data= array();
            $data['user']=$user;
            $data['assets']= base_url('assets/books/');
            // die($data['assets']);
            $this->load->library('template');
            $this->template->front('resetPassword',$data);
         
        }else{
             $this->session->set_flashdata('warning', 'Incorrect or expired token code!');
             header('Location: ' . $_SERVER['HTTP_REFERER']);
        }
    }
    public function sendRestMail()
    {
        if(isset($_SESSION['user_login']))
        {
                redirect(base_url().'books');
        }
        if($_POST)
        {
            $this->load->model('auth_model');
            $email = $this->input->post('email');
            $user = $this->auth_model->get(array('email'=>$email));
               
            if($user[0])
            {
                
             $user = $user[0];
             $UserID   = $user['UserID'];
             $digits = 4;
             $data['name'] = $user['first_name'].' '.$user['last_name'];
             $data['pass'] = $npass = rand(1000000,100000);
             $msg = $this->load->view('mail/reset', $data, true);
             //$msg = $this->Minify_Html($msg);
             $up = array('code'=>$npass);
             $ret = $this->auth_model->updateuserbyid($UserID, $up);
             if($ret)
             {
                 $this->session->userdata('checkMail',$email);
                 $this->template->mail($email,$msg, "Password Change");
                 $this->session->set_flashdata('success', 'Please check your email to reset password code');
                 redirect(base_url().'Auth/confirmCode');
             }
            }else{
                 $this->session->set_flashdata('warning', 'your email does not exit!');
                 header('Location: ' . $_SERVER['HTTP_REFERER']);
            }
            
        }
    }
    function Minify_Html($Html)
	{
	   $Search = array(
	    '/(\n|^)(\x20+|\t)/',
	    '/(\n|^)\/\/(.*?)(\n|$)/',
	    '/\n/',
	    '/\<\!--.*?-->/',
	    '/(\x20+|\t)/', # Delete multispace (Without \n)
	    '/\>\s+\</', # strip whitespaces between tags
	    '/(\"|\')\s+\>/', # strip whitespaces between quotation ("') and end tags
	    '/=\s+(\"|\')/'); # strip whitespaces between = "'

	   $Replace = array(
	    "\n",
	    "\n",
	    " ",
	    "",
	    " ",
	    "><",
	    "$1>",
	    "=$1");

	$Html = preg_replace($Search,$Replace,$Html);
	return $Html;
	}
}

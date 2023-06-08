<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Index extends CI_Controller {
	function __construct() {
        parent::__construct();
        $this->load->model('Book_model');
    }
    public function coupon($id = 0)
    {
        if(isset($_REQUEST['pass']))
        {
            $num = $_REQUEST['pass'];
            $this->load->model('Common_model');
                    $modal = $this->Common_model;
                     $modal->table = 'clients';
                     $user =  $modal->get(array("phone"=>$num));
                     if(isset($user[0]) && isset($user[0]['id']))
                     {
                         $id = $user[0]['id'];
                     }
            
        }
        if($id)
        {     
            $data = array();
            $coupons = array();
                 if(true)
                {
                    $this->load->model('Common_model');
                    $modal = $this->Common_model;
                     $modal->table = 'coupon_item';
                     $coupons =  $modal->get(array("user"=>$id));
                     $data['coupons'] = $coupons;
                }
                if(isset($_REQUEST['pass']))
                {
                    $data= array();
// 		$data['url']= $this->url; 
		$data['assets'] = $assets= base_url('/assets').'/';
		$breed = array();
		$page = 'User coupon';
		$breed['url'] = Base_url('/index/coupon');
		$breed['Home'] = Base_url('/admin/admin');
		// $page = 'Manage Workout Categories';
		$breed[$page] = ' ';
		$css = array();
		$css[] = $assets.'/app-assets/css/core/menu/menu-types/vertical-menu.css';
		$css[] = $assets.'/app-assets/css/core/colors/palette-gradient.css';
		$css[] = $assets.'/app-assets/css/plugins/file-uploaders/dropzone.css';
		$css[] = $assets.'/app-assets/css/pages/data-list-view.css';
		$js = array();
		$js[] = $assets.'/app-assets/js/scripts/ui/data-list-view.js';


		$data['page']= $page;
		$data['c ss']= $css;
		$data['js']= $js;
		$data['breed']= $breed;
                    $data['data'] = $coupons;
                    $this->template->admin('user_coupon',$data);
                }
                else
                {
                $this->template->full('coupon',$data);
                }
        }
        else
        {
            die("invalid link");
        }

    }
    public function single($id = 0)
    {
    	if($_POST)
    	{
    		if(isset($_SESSION['user_login']))
            {
            	$user = $_SESSION['user_login'];
            	$uid = $user->UserID;
            	$rate = $_POST['rate'];
            	$ar = array(
            		'id'=> '',
            		'uid'=> $uid,
            		'bookID'=> $id,
            		'rate'=> $rate,
            	);
            	$this->db->insert('book_to_rate', $ar);
            	redirect($_SERVER['HTTP_REFERER']);
            }
            else
            {
            	redirect(base_url().'/auth/login');
            }
    		
    	}
        if($id)
        {
            $data= array();
            $data['assets']= base_url('assets/books/');
            $data['user'] = $user=$_SESSION['user_login'];  
            $book= $this->Book_model->get(array('bookID'=>$id,'status'=>0));
            if($book[0])
            {
                $book = $book[0];
            }
            else
            {
                redirect('/');
            }

            $data['book'] = $book;
            $coverImg = $this->Book_model->getMediaByID($book['coverImg']);
            $data['book']['img'] = $coverImg->public_id;
            if(isset($book['authorID']))
            {

                $authorID = $this->Book_model->getAuthorByID($book['authorID']);
                
                $data['book']['author'] = $authorID->name;
            }
            $data['book']['lang'] = $lang = $this->Book_model->getLangByBookID($book['bookID']);
            $data['book']['genres'] = $genres = $this->Book_model->getGenreByBookID($book['bookID']);
            $data['book']['tags'] = $tags = $this->Book_model->getTagsByBookID($book['bookID']);
            $data['book']['isborrow'] = $borrow=$this->Book_model->getcheckBookIsBorrow($book['bookID']);
            if($_SESSION['user_login'])
            {
            $user=$_SESSION['user_login'];    
            $data['book']['isRequested']=$this->Book_model->getcheckBookIsRequested($book['bookID'],$user->UserID);
            }else
            $data['book']['isRequested']=$this->Book_model->getcheckBookIsRequested($book['bookID']);
            $data['css'] = array('https://cdnjs.cloudflare.com/ajax/libs/rateYo/2.3.2/jquery.rateyo.min.css');
            $data['scripts'] = array('https://cdnjs.cloudflare.com/ajax/libs/rateYo/2.3.2/jquery.rateyo.min.js',
                base_url('assets/books/js/detail.js')
        );

           
            $this->template->front('detail',$data);
        }
        
    }
    public function borrow($book)
    {
         if(!isset($_SESSION['user_login']))
        {
            redirect(base_url().'Auth/login');
            exit();
            
        }
        $data = array();
        $data['user']= $_SESSION['user_login'];
        $data['data']= $this->Book_model->bookinfo($book);
        $data['assets']= base_url('assets/books/');
        $this->template->front('borrowBook',$data);   
    }

    public function home()
    {

        $data= array();
        $data['assets']= base_url('assets/books/');

        
        $this->load->library('pagination');
        // load URL helper
        $this->load->helper('url');
        $this->load->model('Book_model');
        $data['assets']= base_url('assets/books/');
        $limit_per_page = 12;
        $start_index = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        $total_records = $this->Book_model->get_total();
 
        if ($total_records > 0) 
        {
            // get current page records
            $data["books"] =  $this->Book_model->getHomeProducts($limit_per_page, $start_index);
            $config['base_url'] = base_url() . 'index/home';
            $config['total_rows'] = $total_records;
            $config['per_page'] = $limit_per_page;
            $config["uri_segment"] = 3;
            // custom paging configuration
            $config['num_links'] = 2;
            $config['use_page_numbers'] = TRUE;
            $config['reuse_query_string'] = TRUE;
             
            $config['full_tag_open'] = '<nav aria-label="Page navigation example">
                    <ul class="pagination">';
            $config['full_tag_close'] = '</ul>
                </nav>';
             
            $config['first_link'] = 'First';
            $config['first_tag_open'] = '<li class="page-item">';
            $config['first_tag_close'] = '</li>';
             
            $config['last_link'] = 'Last';
            $config['last_tag_open'] = '<li class="page-item">';
            $config['last_tag_close'] = '</li>';
             
            $config['next_link'] = 'Next';
            $config['next_tag_open'] = '<li class="page-item">';
            $config['next_tag_close'] = '</li>';
 
            $config['prev_link'] = 'Prev';
            $config['prev_tag_open'] = '<li class="page-item">';
            $config['prev_tag_close'] = '</li>';
 
            $config['cur_tag_open'] = '<li class="page-item "><a class="current_page">';
            $config['cur_tag_close'] = '</a></li>';
 
            $config['num_tag_open'] = '<li class="page-item">';
            $config['num_tag_close'] = '</li>';
            $this->pagination->initialize($config);
            // build paging links
            $data["links"] = $this->pagination->create_links();
        }
        $this->load->library('template');
        
        
        $this->template->front('home',$data);

        
    }
    public function aSearch()
	{
		$resp = array();
		echo (isset($_REQUEST['callback']))?$_REQUEST['callback'].'(':'';
		if($_REQUEST['term'])
		{
			
			$txt = $_REQUEST['term'];
			$list = $this->Book_model->searchAuthor($txt);
			foreach ($list as $key => $value) {
				$resp[] = array(
					"id"=>$value['authorID'],
					"value"=>$value['name'],
				);
			}
		}
		echo json_encode($resp);
		echo ');';
	}
	
	public function index()
	{
        user_corse();
		$data= array();
		$data['assets']= base_url('assets/');
		// die($data['assets']);
		$this->load->library('template');
		$this->template->front('home',$data);

		
	}
    public function addbook($id=0)
    {
         if(!isset($_SESSION['user_login']))
        {
             //die('Hrere');
            redirect(base_url().'auth/login');
            
        }
        $user = $this->session->userdata('user_login');
        $data= array();
        $this->load->model('Book_model');
        $this->load->model('Group_model');
        if($id > 0)
        {
             $data['edit']= $this->Book_model->get(array('bookID'=>$id));
            if(isset($data['edit'][0]))
                $data['edit']  = $data['edit'][0];
        }
        
        $data['generes']= $this->Book_model->getGenere(array('status'=>0));

        $data['tags']= $this->Book_model->getTag(array('status'=>0));
        $data['list']= $this->Book_model->getList(array());
        $data['group']=$this->Group_model->get_user_group($user->UserID);
        $data['scripts']= array(
            'https://code.jquery.com/jquery-1.12.4.js',
            'https://code.jquery.com/ui/1.12.1/jquery-ui.js',
            'http://bootstrap-tagsinput.github.io/bootstrap-tagsinput/dist/bootstrap-tagsinput.min.js',
            'https://rawgit.com/select2/select2/master/dist/js/select2.js',
            base_url('assets/admin/pages/').'book.js',
        );
        $data['css']= array(
            'http://bootstrap-tagsinput.github.io/bootstrap-tagsinput/dist/bootstrap-tagsinput.css',
        );
        $data['assets']= base_url('assets/books/');
        $this->template->front('addbook',$data);
    }
	public function page($page = '')
	{
	    $shop = array('login');
	    $full = array('login','signup');
	    if(in_array($page, $shop) && isset($_SESSION['users_login']))
	    {
	        redirect('index/page/shop');
	        exit();
	    }
		$data= array();
		$data['assets']= base_url('assets/');
		// die($data['assets']);
		$this->load->library('template');
		if(in_array($page, $full))
		{
		    $data['assets'] = base_url('/assets/shiri/');
		    $this->load->view('shiri/'.$page,$data);
		}
		else
    	{
    		$this->template->front($page,$data);
        }

	}
	
	public function course($id = '')
	{
		$data= array();
		$data['assets'] = base_url('/assets/shiri/');
		$this->load->library('template');
		$data['sing'] = $this->db->where('id',$id)->get('courses')->row();
		$this->template->front('Overview',$data);

	}
    
    public function module($id = '')
    {
        $data= array();
        $data['assets'] = base_url('/assets/shiri/');
        $this->load->library('template');
        $data['sing'] = $this->db->where('id',$id)->get('module')->row();
        $this->template->front('lessons',$data);

    }
    
    public function lesson($id = '')
    {
        $data= array();
        $data['assets'] = base_url('/assets/shiri/');
        $this->load->library('template');
        $data['sing'] = $this->db->where('id',$id)->get('videos')->row();
        $this->template->front('after-lessons',$data);

    }
    public function saveContact()
    {
        // $this->form_validation->set_rules('uname', 'uname', 'required');
        // $this->form_validation->set_rules('uemail', 'uemail', 'required');
        // $this->form_validation->set_rules('usubject', 'usubject', 'required');
        // $this->form_validation->set_rules('message', 'message', 'required');
        $this->load->model('Login_model');
        // if ($this->form_validation->run() == FALSE)
        // {
        //         $this->session->set_flashdata('error', validation_errors());
        // }
        // else
        // {
            $arr = array(
                "name" => $this->input->post('uname'),
                "email" => $this->input->post('uemail'),
                "subject" => $this->input->post('usubject'),
                "message" => $this->input->post('message'),
            );
            $subject=$this->input->post('usubject');
            if($this->Login_model->saveContactUs($arr))
            {

                $em = array(
                    "name" => $this->input->post('uname'),
                    "email" => $this->input->post('uemail'),
                    "subject" => $subject,
                    "message" => $this->input->post('message'),
                );
                $msg=$this->load->view('mail/contactUSMail', $em, true);
                $email = 'info@shareyourbook.org';
              echo $this->template->mail($email,$msg, $subject); 
                $this->session->set_flashdata('success', 'Your contact mail send  successfully!');
            }
            else
            {
                $this->session->set_flashdata('error', 'server error');
            }
        
        redirect($_SERVER['HTTP_REFERER']);
    }
	public function addgroup()
	{
		// die("OK");
		$data= array();
		$data['assets']= base_url('assets/books/');
		// die($data['assets']);
		$this->load->library('template');
		$this->template->front('addgroup',$data);

		
	}
    public function books()
    {
        $data= array();
        // load Pagination library
        $this->load->library('pagination');
        // load URL helper
        $this->load->helper('url');
        $this->load->model('Book_model');
        $data['assets']= base_url('assets/books/');
        $limit_per_page = 12;
        if($this->input->post('submit')){
            $data['key']=$this->input->post('key');
            $array = array(
                'search_key' => $this->input->post('key'),
            );
            
            $this->session->set_userdata( $array );
            $key=$this->input->post('key');
            $data['assets']= base_url('assets/books/');
            $limit_per_page = 12;
            $start_index = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
            $total_records = count($this->Book_model->fine_search_book($key));
     
            if ($total_records > 0) 
            {
                // get current page records
                $data["books"] =  $this->Book_model->fine_search_book($key,$limit_per_page, $start_index);
                $config['base_url'] = base_url() . 'index/books/';
                $config['total_rows'] = $total_records;
                $config['per_page'] = $limit_per_page;
                $config["uri_segment"] = 3;
                // custom paging configuration
                $config['num_links'] = 2;
                $config['use_page_numbers'] = TRUE;
                $config['reuse_query_string'] = TRUE;
                 
                $config['full_tag_open'] = '<nav aria-label="Page navigation example">
                        <ul class="pagination">';
                $config['full_tag_close'] = '</ul>
                    </nav>';
                 
                $config['first_link'] = 'First';
                $config['first_tag_open'] = '<li class="page-item">';
                $config['first_tag_close'] = '</li>';
                 
                $config['last_link'] = 'Last';
                $config['last_tag_open'] = '<li class="page-item">';
                $config['last_tag_close'] = '</li>';
                 
                $config['next_link'] = 'Next';
                $config['next_tag_open'] = '<li class="page-item">';
                $config['next_tag_close'] = '</li>';
     
                $config['prev_link'] = 'Prev';
                $config['prev_tag_open'] = '<li class="page-item">';
                $config['prev_tag_close'] = '</li>';
     
                $config['cur_tag_open'] = '<li class="page-item "><a class="current_page">';
                $config['cur_tag_close'] = '</a></li>';
     
                $config['num_tag_open'] = '<li class="page-item">';
                $config['num_tag_close'] = '</li>';
                $this->pagination->initialize($config);
                // build paging links
                $data["links"] = $this->pagination->create_links();
            }
            $this->load->library('template');
            $this->template->front('homesearch',$data);
            
        }else if($this->session->userdata('search_key'))
        {

            $data['key']=$this->session->userdata('search_key');
           
            $key=$this->session->userdata('search_key');
            $start_index = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
            $total_records = count($this->Book_model->fine_search_book($key));
     
            if ($total_records > 0) 
            {
                // get current page records
                $data["books"] =  $this->Book_model->fine_search_book($key,$limit_per_page, $start_index);
                $config['base_url'] = base_url() . 'index/books/';
                $config['total_rows'] = $total_records;
                $config['per_page'] = $limit_per_page;
                $config["uri_segment"] = 3;
                // custom paging configuration
                $config['num_links'] = 2;
                $config['use_page_numbers'] = TRUE;
                $config['reuse_query_string'] = TRUE;
                 
                $config['full_tag_open'] = '<nav aria-label="Page navigation example">
                        <ul class="pagination">';
                $config['full_tag_close'] = '</ul>
                    </nav>';
                 
                $config['first_link'] = 'First';
                $config['first_tag_open'] = '<li class="page-item">';
                $config['first_tag_close'] = '</li>';
                 
                $config['last_link'] = 'Last';
                $config['last_tag_open'] = '<li class="page-item">';
                $config['last_tag_close'] = '</li>';
                 
                $config['next_link'] = 'Next';
                $config['next_tag_open'] = '<li class="page-item">';
                $config['next_tag_close'] = '</li>';
     
                $config['prev_link'] = 'Prev';
                $config['prev_tag_open'] = '<li class="page-item">';
                $config['prev_tag_close'] = '</li>';
     
                $config['cur_tag_open'] = '<li class="page-item "><a class="current_page">';
                $config['cur_tag_close'] = '</a></li>';
     
                $config['num_tag_open'] = '<li class="page-item">';
                $config['num_tag_close'] = '</li>';
                $this->pagination->initialize($config);
                // build paging links
                $data["links"] = $this->pagination->create_links();
            }
            $this->load->library('template');
            $this->template->front('homesearch',$data);
        }else{   
        $start_index = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        $total_records = $this->Book_model->get_total(); 
        if ($total_records > 0) 
        {
            // get current page records
            $data["books"] =  $this->Book_model->getHomeProducts($limit_per_page, $start_index);
            $config['base_url'] = base_url() . 'index/books';
            $config['total_rows'] = $total_records;
            $config['per_page'] = $limit_per_page;
            $config["uri_segment"] = 3;
            // custom paging configuration
            $config['num_links'] = 2;
            $config['use_page_numbers'] = TRUE;
            $config['reuse_query_string'] = TRUE;
             
            $config['full_tag_open'] = '<nav aria-label="Page navigation example">
                    <ul class="pagination">';
            $config['full_tag_close'] = '</ul>
                </nav>';
             
            $config['first_link'] = 'First';
            $config['first_tag_open'] = '<li class="page-item">';
            $config['first_tag_close'] = '</li>';
             
            $config['last_link'] = 'Last';
            $config['last_tag_open'] = '<li class="page-item">';
            $config['last_tag_close'] = '</li>';
             
            $config['next_link'] = 'Next';
            $config['next_tag_open'] = '<li class="page-item">';
            $config['next_tag_close'] = '</li>';
 
            $config['prev_link'] = 'Prev';
            $config['prev_tag_open'] = '<li class="page-item">';
            $config['prev_tag_close'] = '</li>';
 
            $config['cur_tag_open'] = '<li class="page-item "><a class="current_page">';
            $config['cur_tag_close'] = '</a></li>';
 
            $config['num_tag_open'] = '<li class="page-item">';
            $config['num_tag_close'] = '</li>';
            $this->pagination->initialize($config);
            // build paging links
            $data["links"] = $this->pagination->create_links();
        }
        $start_index = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        $total_records = $this->Book_model->get_total();
        $this->load->library('template');
        $this->template->front('homesearch',$data);
        }
    }
    public function availableBook()
    {
        $data= array();
        // load Pagination library
        $this->load->library('pagination');
        // load URL helper
        $this->load->helper('url');
        $this->load->model('Book_model');
        $data['assets']= base_url('assets/books/');
        $limit_per_page = 12;
        if($this->input->post('submit')){
            $data['key']=$this->input->post('key');
            $array = array(
                'search_key_' => $this->input->post('key'),
            );
            
            $this->session->set_userdata( $array );
            $key=$this->input->post('key');
            $start_index = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;

            $total_records = count($this->Book_model->get_available_book_total(0,0,$key));
            if ($total_records > 0) 
            {
                // get current page records

                $data["books"] =  $this->Book_model->get_available_book_total($limit_per_page, $start_index,$key);
                $config['base_url'] = base_url() . 'index/availableBook/';
                $config['total_rows'] = $total_records;
                $config['per_page'] = $limit_per_page;
                $config["uri_segment"] = 3;
                // custom paging configuration
                $config['num_links'] = 2;
                $config['use_page_numbers'] = TRUE;
                $config['reuse_query_string'] = TRUE;
                 
                $config['full_tag_open'] = '<nav aria-label="Page navigation example">
                        <ul class="pagination">';
                $config['full_tag_close'] = '</ul>
                    </nav>';
                 
                $config['first_link'] = 'First';
                $config['first_tag_open'] = '<li class="page-item">';
                $config['first_tag_close'] = '</li>';
                 
                $config['last_link'] = 'Last';
                $config['last_tag_open'] = '<li class="page-item">';
                $config['last_tag_close'] = '</li>';
                 
                $config['next_link'] = 'Next';
                $config['next_tag_open'] = '<li class="page-item">';
                $config['next_tag_close'] = '</li>';
     
                $config['prev_link'] = 'Prev';
                $config['prev_tag_open'] = '<li class="page-item">';
                $config['prev_tag_close'] = '</li>';
     
                $config['cur_tag_open'] = '<li class="page-item "><a class="current_page">';
                $config['cur_tag_close'] = '</a></li>';
     
                $config['num_tag_open'] = '<li class="page-item">';
                $config['num_tag_close'] = '</li>';
                $this->pagination->initialize($config);
                // build paging links
                $data["links"] = $this->pagination->create_links();
            }
            
        }else if($this->session->userdata('search_key_'))
        {

            $data['key']=$this->session->userdata('search_key_');
           
            $key=$this->session->userdata('search_key_');
            $start_index = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
            $total_records = count($this->Book_model->get_available_book_total(0,0,$key));
     
            if ($total_records > 0) 
            {
                // get current page records
                $data["books"] =  $this->Book_model->get_available_book_total($limit_per_page, $start_index,$key);
                $config['base_url'] = base_url() . 'index/availableBook/';
                $config['total_rows'] = $total_records;
                $config['per_page'] = $limit_per_page;
                $config["uri_segment"] = 3;
                // custom paging configuration
                $config['num_links'] = 2;
                $config['use_page_numbers'] = TRUE;
                $config['reuse_query_string'] = TRUE;
                 
                $config['full_tag_open'] = '<nav aria-label="Page navigation example">
                        <ul class="pagination">';
                $config['full_tag_close'] = '</ul>
                    </nav>';
                 
                $config['first_link'] = 'First';
                $config['first_tag_open'] = '<li class="page-item">';
                $config['first_tag_close'] = '</li>';
                 
                $config['last_link'] = 'Last';
                $config['last_tag_open'] = '<li class="page-item">';
                $config['last_tag_close'] = '</li>';
                 
                $config['next_link'] = 'Next';
                $config['next_tag_open'] = '<li class="page-item">';
                $config['next_tag_close'] = '</li>';
     
                $config['prev_link'] = 'Prev';
                $config['prev_tag_open'] = '<li class="page-item">';
                $config['prev_tag_close'] = '</li>';
     
                $config['cur_tag_open'] = '<li class="page-item "><a class="current_page">';
                $config['cur_tag_close'] = '</a></li>';
     
                $config['num_tag_open'] = '<li class="page-item">';
                $config['num_tag_close'] = '</li>';
                $this->pagination->initialize($config);
                // build paging links
                $data["links"] = $this->pagination->create_links();
            }
            $this->load->library('template');
            $this->template->front('availableBook',$data);
        }else{
        $start_index = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        $total_records = count($this->Book_model->get_available_book_total()); 
        if ($total_records > 0) 
        {
            // get current page records
            $data["books"] =  $this->Book_model->get_available_book_total($limit_per_page, $start_index);
            $config['base_url'] = base_url() . 'index/availableBook';
            $config['total_rows'] = $total_records;
            $config['per_page'] = $limit_per_page;
            $config["uri_segment"] = 3;
            // custom paging configuration
            $config['num_links'] = 2;
            $config['use_page_numbers'] = TRUE;
            $config['reuse_query_string'] = TRUE;
             
            $config['full_tag_open'] = '<nav aria-label="Page navigation example">
                    <ul class="pagination">';
            $config['full_tag_close'] = '</ul>
                </nav>';
             
            $config['first_link'] = 'First';
            $config['first_tag_open'] = '<li class="page-item">';
            $config['first_tag_close'] = '</li>';
             
            $config['last_link'] = 'Last';
            $config['last_tag_open'] = '<li class="page-item">';
            $config['last_tag_close'] = '</li>';
             
            $config['next_link'] = 'Next';
            $config['next_tag_open'] = '<li class="page-item">';
            $config['next_tag_close'] = '</li>';
 
            $config['prev_link'] = 'Prev';
            $config['prev_tag_open'] = '<li class="page-item">';
            $config['prev_tag_close'] = '</li>';
 
            $config['cur_tag_open'] = '<li class="page-item "><a class="current_page">';
            $config['cur_tag_close'] = '</a></li>';
 
            $config['num_tag_open'] = '<li class="page-item">';
            $config['num_tag_close'] = '</li>';
            $this->pagination->initialize($config);
            // build paging links
            $data["links"] = $this->pagination->create_links();
        }
        
        }
        $this->load->library('template');
        $this->template->front('availableBook',$data);
    }

    public function search()
    {
        $data = array();
        $user = $_SESSION['user_login'];
        // load Pagination library
        $this->load->library('pagination');
        // load URL helper
        $this->load->helper('url');
        $this->load->model('Book_model');
        $key=$this->input->get('key');
        $data['key']=$key;
        $data['assets']= base_url('assets/books/');
        $limit_per_page = 12;
        $start_index = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        $total_records = count($this->Book_model->fine_search_book($key));
 
        if ($total_records > 0) 
        {
            // get current page records
            $data["books"] =  $this->Book_model->fine_search_book($key,$limit_per_page, $start_index);
            $config['base_url'] = base_url() . 'index/search';
            $config['total_rows'] = $total_records;
            $config['per_page'] = $limit_per_page;
            $config["uri_segment"] = 3;
            // custom paging configuration
            $config['num_links'] = 2;
            $config['use_page_numbers'] = TRUE;
            $config['reuse_query_string'] = TRUE;
             
            $config['full_tag_open'] = '<nav aria-label="Page navigation example">
                    <ul class="pagination">';
            $config['full_tag_close'] = '</ul>
                </nav>';
             
            $config['first_link'] = 'First';
            $config['first_tag_open'] = '<li class="page-item">';
            $config['first_tag_close'] = '</li>';
             
            $config['last_link'] = 'Last';
            $config['last_tag_open'] = '<li class="page-item">';
            $config['last_tag_close'] = '</li>';
             
            $config['next_link'] = 'Next';
            $config['next_tag_open'] = '<li class="page-item">';
            $config['next_tag_close'] = '</li>';
 
            $config['prev_link'] = 'Prev';
            $config['prev_tag_open'] = '<li class="page-item">';
            $config['prev_tag_close'] = '</li>';
 
            $config['cur_tag_open'] = '<li class="page-item "><a class="current_page">';
            $config['cur_tag_close'] = '</a></li>';
 
            $config['num_tag_open'] = '<li class="page-item">';
            $config['num_tag_close'] = '</li>';
            $this->pagination->initialize($config);
            // build paging links
            $data["links"] = $this->pagination->create_links();
        }
        
        $data['assets']= base_url('assets/books/');
        $this->template->front('searchBook',$data);
    }
    public function accept_invite()
    {
        $token=$_GET['token'];
        $request=$_GET['requestno'];
        $this->load->model('Group_model');
        $check=[
            'token'=>$token,
            'id'=>$request,
        ];
        $response=$this->Group_model->getMemberToken($check);
        $user = $this->session->userdata('user_login');
        if($this->Group_model->usersbymail($response->email))
        {
            if(isset($user) && $user->email == $response->email)
            {    
                redirect(base_url().'Groups/Invition');
            }else{
                $this->session->set_flashdata('success', 'Please login please first!');
                session_destroy();
                unset($_SESSION['user_login']);
                redirect(base_url().'auth/login');
            }
        }
        else
        {
            $this->session->set_flashdata('success', 'Please Register first!');
            session_destroy();
            unset($_SESSION['user_login']);
            redirect(base_url().'auth/register');
        }
        $this->session->set_flashdata('error', 'server error');
        redirect(base_url());
    }
}

<?php



defined('BASEPATH') OR exit('No direct script access allowed');







class Course extends CI_Controller {



		 function __construct() {

        parent::__construct();

        if(!isset($_SESSION['knet_login']))

		{

// 			redirect('/login'); 

		}

        $data= array();

		$this->load->library('template');

		$this->load->model('Common_model');

		$this->Common_model->table = 'courses';

		$this->modal = $this->Common_model;

		$this->url = base_url('/admin/course');

		$this->load->model('auth_model');

    }

    //variables

    private $single= 'Course';

    private $multi= 'Courses';

    private $add= 'addmusic';

    private $all= 'allmusic';

    private $url= '';

    private $modal= '';

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



	

	public function save($id = 0)

	{

        $this->form_validation->set_rules('name', $this->single.' Name', 'required');

        if(!isset($_FILES['image']) || !$_FILES['image'])

        {

	        if(!isset($_FILES['image']['name']) && $id  == 0)

	        {

	        	$this->form_validation->set_rules('image', $this->single.' Image', 'required');

	        }

	    }

        if ($this->form_validation->run() == FALSE)

        {

                $this->session->set_flashdata('error', validation_errors());

        }

        else

        {

        	$mediaID = 0;

        	if(isset($_FILES['image']['name']) && !empty($_FILES['image']['name']))

	        {

	        	$imgData = $this->template->upload('image');

	        	if($imgData['localPath'])

	        	{

	        		$mediaID = $this->modal->addMedia($imgData);

	        	}



	        	if($id && $mediaID > 0 && !empty($imgData['localPath']))

	        	{

  	        		$this->modal->update($id,array('mediaID'=>$mediaID));

	        	}

	        }

        	$banner = 0;

        	if(isset($_FILES['banner']['name']) && !empty($_FILES['banner']['name']))

	        {

	        	$imgData = $this->template->upload('banner');

	        	if($imgData['localPath'])

	        	{

	        		$banner = $this->modal->addMedia($imgData);

	        	}



	        	if($id && $banner > 0 && !empty($imgData['localPath']))

	        	{

  	        		$this->modal->update($id,array('banner'=>$banner));

	        	}

	        }

        	$user = $this->session->userdata('knet_login');

        	if($id)

        	{

        		$arr = array(

	        		"name" => $this->input->post('name'),

	        		"detail" => $this->input->post('detail'),

	        		"price" => $this->input->post('price'),

	        		"badge" => $this->input->post('badge'),

	        		"cover" => $this->input->post('cover'),

	        		"credit" => $this->input->post('credit'),

	       // 		"userID" => $user->UserID,

	        		

	        	);

        		if($this->modal->update($id,$arr))

	        	{

	                $this->session->set_flashdata('success', $this->single.' update successfully!');

	        	}

	        	else

	        	{

	        	    echo $this->db->last_query();

	        	    die();

	        		$this->session->set_flashdata('error', 'server error');

	        	}

        	}

        	else

        	{

        		$arr = array(

	        		"name" => $this->input->post('name'),

	        		"detail" => $this->input->post('detail'),

	        		"price" => $this->input->post('price'),

	        		"badge" => $this->input->post('badge'),

	        		"cover" => $this->input->post('cover'),

	        		"userID" => $user->UserID,

	        		"mediaID" => $mediaID,

	        		"banner" => $banner,

	        		"credit" => $this->input->post('credit'),

	        	);

	        	if($this->modal->add($arr))

	        	{

	                $this->session->set_flashdata('success', $this->single.' add successfully!');

	        	}

	        	else

	        	{

	        		$this->session->set_flashdata('error', 'server error');

	        	}

	        }

        } 

        // print_r($_SERVER);

        // die();

        redirect($this->url.'/all');

	}



	public function delete($id = 0)

	{

		if($id)

		{

			if($this->modal->update($id, array('status'=> 1)))

        	{

                $this->session->set_flashdata('success', $this->single.' delete successfully!');

        	}

        	else

        	{

        		$this->session->set_flashdata('error', 'server error');

        	}

		}

		redirect($_SERVER['HTTP_REFERER']);

	}



	public function create($id= 0)

	{

		$data= array();

		$data['url']= $this->url;

		$data['assets']= base_url('assets/admin/');

		$breed = array();

		$page = 'Add '.$this->single;

		$breed['Home'] = Base_url('/admin/admin');

		$breed[$page] = '';



		$data['page']= $this->single;

		$data['breed']= $breed;

		if($id)

		{



			$data['edit']= $this->modal->getbyid($id);

		}

		$this->template->admin($this->add,$data);

	}



	public function all()

	{

		$data= array();

		$data['url']= $this->url; 

		$data['assets'] = $assets= base_url('/assets').'/';

		$breed = array();

		$breed['Home'] = Base_url('/admin/admin');

		$page = 'Manage '.$this->single;

		$breed[$page] = '';

		$css = array();

		$css[] = $assets.'/app-assets/css/core/menu/menu-types/vertical-menu.css';

		$css[] = $assets.'/app-assets/css/core/colors/palette-gradient.css';

		$css[] = $assets.'/app-assets/css/plugins/file-uploaders/dropzone.css';

		$css[] = $assets.'/app-assets/css/pages/data-list-view.css';

		$js = array();

		$js[] = $assets.'/app-assets/js/scripts/ui/data-list-view.js';





		$data['page']= $page;

		$data['css']= $css;

		$data['js']= $js;

		$data['breed']= $breed;

		$data['data']= $this->modal->get(array('status'=> 0));

		$this->template->admin($this->all,$data);

	}







}




<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'libraries/importers/Amazon_uk.php';

class Amazon_au extends Amazon_uk {

    protected $fetchLanguage = 'en-AU,en;q=0.9';
}

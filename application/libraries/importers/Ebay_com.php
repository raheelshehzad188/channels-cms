<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'libraries/importers/Ebay_uk.php';

class Ebay_com extends Ebay_uk {

    protected $fetchLanguage = 'en-US,en;q=0.9';
}

<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'modules/store/controllers/Store_base.php';

class Homepage_hero extends Store_base {

    public function index()
    {
        redirect('store/theme-settings/slider');
    }

    public function form($id = 0)
    {
        redirect($id ? 'store/theme-settings/slide/' . (int) $id : 'store/theme-settings/slide');
    }

    public function delete($id = 0)
    {
        redirect('store/theme-settings/slide-delete/' . (int) $id);
    }
}

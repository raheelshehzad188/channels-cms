<?php

class Login_model extends CI_Model {

    public function login($uname, $upass)
    {
        $this->db->group_start();
        $this->db->where('email', $uname);
        $this->db->or_where('uname', $uname);
        $this->db->group_end();
        $this->db->where('upass', md5($upass));
        $this->db->where('status', 1);
        return $this->db->get('users')->row();
    }

    public function updateuserbyid($userID, $data)
    {
        return $this->db->where('UserID', $userID)->update('users', $data);
    }

    public function getrolebyid($roleID)
    {
        return $this->db->where('roleID', $roleID)->get('roles')->row();
    }

    public function saveContactUs($data)
    {
        $this->db->insert('contact_us', $data);
        return $this->db->insert_id();
    }
}

<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model {

    protected $table = 'users';

    public function all()
    {
        return $this->db
            ->select('users.*, roles.name as role_name')
            ->from($this->table)
            ->join('roles', 'roles.roleID = users.roleID', 'left')
            ->order_by('users.UserID', 'desc')
            ->get()
            ->result();
    }

    public function get($id)
    {
        return $this->db->where('UserID', (int) $id)->get($this->table)->row();
    }

    public function roles()
    {
        return $this->db->order_by('roleID', 'asc')->get('roles')->result();
    }

    public function exists_email($email, $ignoreId = 0)
    {
        $this->db->where('email', $email);
        if ($ignoreId) {
            $this->db->where('UserID !=', (int) $ignoreId);
        }
        return $this->db->count_all_results($this->table) > 0;
    }

    public function exists_uname($uname, $ignoreId = 0)
    {
        $this->db->where('uname', $uname);
        if ($ignoreId) {
            $this->db->where('UserID !=', (int) $ignoreId);
        }
        return $this->db->count_all_results($this->table) > 0;
    }

    public function save($data, $id = 0)
    {
        if ($id) {
            $this->db->where('UserID', (int) $id)->update($this->table, $data);
            return (int) $id;
        }
        $this->db->insert($this->table, $data);
        return (int) $this->db->insert_id();
    }

    public function delete($id)
    {
        return $this->db->where('UserID', (int) $id)->delete($this->table);
    }
}

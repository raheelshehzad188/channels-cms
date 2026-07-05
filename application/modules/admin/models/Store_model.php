<?php
class Store_model extends CI_Model {
        private $table = 'stores';

        public function get($data = array())
        {
                $this->db->order_by('id', 'desc');
                if (!$data) {
                        return $this->db->get($this->table)->result_array();
                }
                return $this->db->where($data)->get($this->table)->result_array();
        }

        public function getbyid($id)
        {
                return $this->db->where('id', $id)->get($this->table)->row();
        }

        public function update($id, $data)
        {
                return $this->db->where('id', $id)->update($this->table, $data);
        }

        public function add($data)
        {
                $this->db->insert($this->table, $data);
                return $this->db->insert_id();
        }
}

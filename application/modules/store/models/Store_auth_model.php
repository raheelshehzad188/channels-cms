<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Store_auth_model extends CI_Model {

    public function authenticate($email, $password, $domain = '')
    {
        $email = strtolower(trim($email));
        $hash = md5(trim($password));
        $domain = $this->normalize_domain($domain);

        $this->db->where('email', $email)->where('status', 1);
        if ($domain !== '') {
            $this->db->group_start()
                ->where('domain', $domain)
                ->or_where('domain', 'www.' . $domain)
                ->group_end();
        }
        $store = $this->db->get('stores')->row();

        if (!$store) {
            $store = $this->db->where('email', $email)->where('status', 1)->get('stores')->row();
        }

        if ($store && hash_equals((string) $store->password, $hash)) {
            return array('store' => $store, 'staff' => $this->ownerStaff($store, $email));
        }

        $staff = $this->db
            ->where('email', $email)
            ->where('status', 1)
            ->get('staff')
            ->row();

        if ($staff && hash_equals((string) $staff->password, $hash)) {
            $store = $this->db->where('id', $staff->store_id)->where('status', 1)->get('stores')->row();
            if ($store && ($domain === '' || $store->domain === $domain || $store->domain === 'www.' . $domain)) {
                return array('store' => $store, 'staff' => $staff);
            }
        }

        return false;
    }

    public function createSession($storeId, $staffId, $token, $expiresAt)
    {
        return $this->db->insert('store_sessions', array(
            'store_id' => $storeId,
            'staff_id' => $staffId ?: null,
            'session_token' => $token,
            'ip_address' => $this->input->ip_address(),
            'user_agent' => substr((string) $this->input->user_agent(), 0, 500),
            'expires_at' => $expiresAt,
        ));
    }

    public function setResetToken($storeId, $token, $expires)
    {
        return $this->db->where('id', $storeId)->update('stores', array(
            'reset_token' => $token,
            'reset_expires' => $expires,
        ));
    }

    public function getByResetToken($token)
    {
        return $this->db
            ->where('reset_token', $token)
            ->where('reset_expires >=', date('Y-m-d H:i:s'))
            ->where('status', 1)
            ->get('stores')
            ->row();
    }

    public function clearResetToken($storeId)
    {
        return $this->db->where('id', $storeId)->update('stores', array(
            'reset_token' => null,
            'reset_expires' => null,
        ));
    }

    protected function ownerStaff($store, $email)
    {
        return (object) array(
            'id' => 0,
            'store_id' => $store->id,
            'name' => !empty($store->owner_name) ? $store->owner_name : $store->name,
            'email' => $email,
            'role' => 'owner',
            'permissions' => '["*"]',
        );
    }

    protected function normalize_domain($domain)
    {
        $domain = strtolower(trim($domain));
        $domain = preg_replace('/^https?:\/\//', '', $domain);
        $domain = rtrim($domain, '/');
        if (preg_match('/^(theme[12]|fruitables|zenvello)\.localhost$/', $domain, $match)) {
            return $match[1] . '.ecommerce.test';
        }
        if (strpos($domain, 'www.') === 0) {
            $domain = substr($domain, 4);
        }
        return $domain;
    }
}

<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Accounting extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        ec_require_products();
        $this->load->model('Accounting_model');
        $this->load->model('Ec_order_model');
        $this->Ec_order_model->ensure_item_columns();
        $this->Accounting_model->backfill_completed_items();
    }

    public function index()
    {
        $platformCurrency = platform_currency();
        $data = array(
            'title' => 'Accounting',
            'is_admin' => ec_is_admin(),
            'platform_currency' => $platformCurrency,
            'vat_total' => $this->Accounting_model->platform_vat_total(),
            'stores' => array(),
            'store_totals' => array(),
            'ecommerce_rows' => array(),
            'summary' => null,
            'payouts' => array(),
            'bank' => null,
            'ledger' => array(),
        );

        if (ec_is_admin()) {
            $stores = $this->Accounting_model->store_summaries();
            $data['stores'] = $stores['rows'];
            $data['store_totals'] = $stores['totals'];
            $data['ecommerce_rows'] = $this->Accounting_model->ecommerce_summaries();
            $data['payouts'] = $this->Accounting_model->payouts(array('status' => 'pending'));
        } else {
            ensure_whatsapp_schema();
            $userId = (int) ec_user()->UserID;
            $user = $this->db->where('UserID', $userId)->get('users')->row();
            $data['summary'] = $this->Accounting_model->party_summary('ecommerce', $userId);
            $data['bank'] = $this->Accounting_model->bank('ecommerce', $userId);
            $data['ledger'] = $this->Accounting_model->ledger('ecommerce', $userId, 30);
            $data['payouts'] = $this->Accounting_model->payouts(array(
                'party_type' => 'ecommerce',
                'party_id' => $userId,
            ));
            $data['whatsapp_number'] = $user && !empty($user->whatsapp_number)
                ? $user->whatsapp_number
                : ($user && !empty($user->phone) ? $user->phone : '');
        }

        $this->template->admin('accounting/index', $data);
    }

    public function payouts()
    {
        ec_require_admin();
        $status = trim((string) $this->input->get('status'));
        $filters = array();
        if ($status !== '') {
            $filters['status'] = $status;
        }
        $data = array(
            'title' => 'Payouts',
            'payouts' => $this->Accounting_model->payouts($filters),
            'status' => $status,
            'platform_currency' => platform_currency(),
        );
        $this->template->admin('accounting/payouts', $data);
    }

    public function process_payout($id = 0)
    {
        ec_require_admin();
        $action = trim((string) $this->input->post('action'));
        $note = trim((string) $this->input->post('admin_note'));
        $status = $action === 'reject' ? 'rejected' : 'paid';
        $receipt = '';
        if ($status === 'paid') {
            $receipt = $this->upload_receipt();
            if ($receipt === false) {
                $this->session->set_flashdata('error', 'Slip upload failed. Use JPG, PNG, WEBP or PDF.');
                redirect('admin/accounting/payouts');
                return;
            }
            if ($receipt === '') {
                $this->session->set_flashdata('error', 'Please upload a payment slip before marking as paid.');
                redirect('admin/accounting/payouts');
                return;
            }
        }
        $result = $this->Accounting_model->process_payout($id, $status, (int) ec_user()->UserID, $note, $receipt);
        if ($result === true) {
            $this->session->set_flashdata('success', $status === 'paid' ? 'Payout marked as paid.' : 'Payout request rejected and wallet credited back.');
        } else {
            $this->session->set_flashdata('error', is_string($result) ? $result : 'Unable to process payout.');
        }
        redirect('admin/accounting/payouts');
    }

    protected function upload_receipt()
    {
        if (empty($_FILES['receipt']['name'])) {
            return '';
        }
        $dir = FCPATH . 'uploads/payouts/';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $config = array(
            'upload_path' => $dir,
            'allowed_types' => 'jpg|jpeg|png|gif|webp|pdf',
            'max_size' => 5120,
            'encrypt_name' => true,
        );
        $this->load->library('upload', $config);
        $this->upload->initialize($config);
        if (!$this->upload->do_upload('receipt')) {
            return false;
        }
        $uploaded = $this->upload->data();
        return 'uploads/payouts/' . $uploaded['file_name'];
    }

    public function save_account()
    {
        if (!ec_is_ecommerce()) {
            $this->session->set_flashdata('error', 'Only ecommerce users can save account details here.');
            redirect('admin/accounting');
            return;
        }
        $this->Accounting_model->save_bank('ecommerce', (int) ec_user()->UserID, $this->input->post());
        $this->session->set_flashdata('success', 'Account details saved.');
        redirect('admin/accounting');
    }

    public function save_whatsapp()
    {
        if (!ec_is_ecommerce()) {
            $this->session->set_flashdata('error', 'Only ecommerce users can save a WhatsApp number here.');
            redirect('admin/accounting');
            return;
        }
        ensure_whatsapp_schema();
        $number = trim((string) $this->input->post('whatsapp_number'));
        $userId = (int) ec_user()->UserID;
        $this->db->where('UserID', $userId)->update('users', array('whatsapp_number' => $number));
        if (!empty($_SESSION['knet_login'])) {
            $_SESSION['knet_login']->whatsapp_number = $number;
        }
        $this->session->set_flashdata('success', 'WhatsApp number saved.');
        redirect('admin/accounting');
    }

    public function request_payout()
    {
        if (!ec_is_ecommerce()) {
            $this->session->set_flashdata('error', 'Only ecommerce users can request a payout here.');
            redirect('admin/accounting');
            return;
        }
        $amount = (float) $this->input->post('amount');
        $result = $this->Accounting_model->request_payout('ecommerce', (int) ec_user()->UserID, $amount);
        if (is_int($result)) {
            $this->session->set_flashdata('success', 'Payout request submitted.');
        } else {
            $this->session->set_flashdata('error', $result);
        }
        redirect('admin/accounting');
    }
}

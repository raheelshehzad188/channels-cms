<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'modules/store/controllers/Store_base.php';

class Accounting extends Store_base {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Accounting_model');
        $this->load->model('Ec_order_model');
    }

    public function index()
    {
        $this->requireAuth();
        $this->requirePermission('accounting');
        $this->Ec_order_model->ensure_item_columns();
        $this->Accounting_model->backfill_completed_items();

        $storeId = (int) $this->store->id;
        $allPayouts = $this->Accounting_model->payouts(array(
            'party_type' => 'store',
            'party_id' => $storeId,
        ));
        $received = array();
        $openPayouts = array();
        foreach ($allPayouts as $payout) {
            if ($payout->status === 'paid') {
                $received[] = $payout;
            } else {
                $openPayouts[] = $payout;
            }
        }
        $this->template->store('accounting/index', $this->viewData(array(
            'page' => 'Accounting',
            'title' => 'Accounting',
            'summary' => $this->Accounting_model->party_summary('store', $storeId),
            'bank' => $this->Accounting_model->bank('store', $storeId),
            'ledger' => $this->Accounting_model->ledger('store', $storeId, 40),
            'payouts' => $openPayouts,
            'received_payouts' => $received,
        )));
    }

    public function save_bank()
    {
        $this->requireAuth();
        $this->requirePermission('accounting');
        $this->Accounting_model->save_bank('store', (int) $this->store->id, $this->input->post());
        $this->session->set_flashdata('success', 'Account details saved.');
        redirect('store/accounting');
    }

    public function request_payout()
    {
        $this->requireAuth();
        $this->requirePermission('accounting');
        $amount = (float) $this->input->post('amount');
        $result = $this->Accounting_model->request_payout('store', (int) $this->store->id, $amount);
        if (is_int($result)) {
            $this->session->set_flashdata('success', 'Payout request submitted.');
        } else {
            $this->session->set_flashdata('error', $result);
        }
        redirect('store/accounting');
    }
}

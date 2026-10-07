<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'modules/store/controllers/Store_base.php';

class Channels extends Store_base {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Store_channel_model');
        $this->load->library(array('meta_commerce', 'tiktok_commerce'));
        $this->Store_channel_model->ensure_schema();
    }

    public function index()
    {
        $this->requireAuth();
        $this->requirePermission('apps');

        $meta = $this->Store_channel_model->get($this->store->id, 'meta');
        $tiktok = $this->Store_channel_model->get($this->store->id, 'tiktok');
        if ($meta && $meta->access_token !== '' && $meta->status !== 'disconnected') {
            $this->Store_channel_model->refresh_token_if_needed($meta);
            $meta = $this->Store_channel_model->get($this->store->id, 'meta');
        }
        if ($tiktok && $tiktok->access_token !== '' && $tiktok->status !== 'disconnected') {
            $this->Store_channel_model->refresh_token_if_needed($tiktok);
            $tiktok = $this->Store_channel_model->get($this->store->id, 'tiktok');
        }

        $this->template->store('channels/index', $this->viewData(array(
            'page' => 'Sales Channels',
            'title' => 'Sales Channels',
            'meta_ready' => $this->meta_commerce->configured(),
            'tiktok_ready' => $this->tiktok_commerce->configured(),
            'meta' => $this->Store_channel_model->public_row($meta),
            'tiktok' => $this->Store_channel_model->public_row($tiktok),
            'meta_assets' => $this->meta_asset_options($meta),
            'tiktok_assets' => $this->tiktok_asset_options($tiktok),
        )));
    }

    public function meta($action = '')
    {
        $action = strtolower((string) $action);
        if ($action === 'callback') {
            $this->meta_callback();
            return;
        }
        $this->requireAuth();
        $this->requirePermission('apps');
        if ($action === 'connect') {
            $this->meta_connect();
            return;
        }
        if ($action === 'save') {
            $this->meta_save_assets();
            return;
        }
        if ($action === 'sync') {
            $this->run_sync('meta');
            return;
        }
        if ($action === 'disconnect') {
            if (strtoupper((string) $this->input->method()) !== 'POST') {
                redirect('store/channels');
                return;
            }
            $conn = $this->Store_channel_model->get($this->store->id, 'meta');
            if ($conn && $conn->access_token !== '') {
                $this->meta_commerce->revoke($conn->access_token);
            }
            $this->Store_channel_model->disconnect($this->store->id, 'meta');
            $this->session->set_flashdata('success', 'Meta account disconnected.');
            redirect('store/channels');
            return;
        }
        redirect('store/channels');
    }

    public function tiktok($action = '')
    {
        $action = strtolower((string) $action);
        if ($action === 'callback') {
            $this->tiktok_callback();
            return;
        }
        $this->requireAuth();
        $this->requirePermission('apps');
        if ($action === 'connect') {
            $this->tiktok_connect();
            return;
        }
        if ($action === 'save') {
            $this->tiktok_save_assets();
            return;
        }
        if ($action === 'sync') {
            $this->run_sync('tiktok');
            return;
        }
        if ($action === 'disconnect') {
            if (strtoupper((string) $this->input->method()) !== 'POST') {
                redirect('store/channels');
                return;
            }
            $conn = $this->Store_channel_model->get($this->store->id, 'tiktok');
            if ($conn && $conn->access_token !== '') {
                $this->tiktok_commerce->revoke($conn->access_token);
            }
            $this->Store_channel_model->disconnect($this->store->id, 'tiktok');
            $this->session->set_flashdata('success', 'TikTok account disconnected.');
            redirect('store/channels');
            return;
        }
        redirect('store/channels');
    }

    protected function meta_connect()
    {
        if (!$this->meta_commerce->configured()) {
            $this->session->set_flashdata('error', 'Meta is not configured by the platform admin yet.');
            redirect('store/channels');
            return;
        }
        $state = $this->Store_channel_model->make_state($this->store->id, 'meta');
        $this->session->set_userdata('channel_oauth_state', $state);
        redirect($this->meta_commerce->oauth_url($state, $this->store, 'channels'));
    }

    protected function meta_callback()
    {
        $this->requireAuth();
        $this->requirePermission('apps');
        $error = $this->input->get('error_description') ?: $this->input->get('error');
        $this->meta_commerce->oauth_trace('callback_received', array(
            'purpose' => 'channels',
            'ok' => !($error !== '' && $error !== null),
            'has_code' => trim((string) $this->input->get('code')) !== '',
            'has_error' => $error !== '' && $error !== null,
            'error' => $error ? $this->meta_commerce->redact((string) $error) : '',
            'redirect_uri' => $this->meta_commerce->redirect_uri($this->store),
            'scopes' => $this->meta_commerce->oauth_scopes('channels'),
        ), $this->store);
        if ($error) {
            $this->session->set_flashdata('error', $this->oauth_error_message($error));
            redirect('store/channels');
            return;
        }
        $state = $this->Store_channel_model->read_state($this->input->get('state'), 'meta', $this->store->id);
        if (!$state || (int) $state['s'] !== (int) $this->store->id || $state['p'] !== 'meta') {
            $this->session->set_flashdata('error', 'Meta login expired. Please connect again.');
            redirect('store/channels');
            return;
        }
        $code = trim((string) $this->input->get('code'));
        if ($code === '') {
            $this->session->set_flashdata('error', 'Meta did not return a login code.');
            redirect('store/channels');
            return;
        }
        $token = $this->meta_commerce->exchange_code($code);
        if (!$token) {
            $this->session->set_flashdata('error', $this->meta_commerce->last_error());
            redirect('store/channels');
            return;
        }

        $payload = array(
            'status' => 'needs_assets',
            'access_token' => $token['access_token'],
            'token_expires_at' => date('Y-m-d H:i:s', time() + (int) $token['expires_in']),
            'external_user_id' => $token['user_id'],
            'connected_at' => date('Y-m-d H:i:s'),
            'last_error' => '',
            'extra' => array('user_name' => $token['user_name']),
        );

        $fbe = $this->meta_commerce->fbe_installs($token['access_token'], $this->store->id);
        $installs = array();
        if (!empty($fbe['data']) && is_array($fbe['data'])) {
            $installs = $fbe['data'];
        }
        if (!empty($installs[0])) {
            $row = $installs[0];
            if (!empty($row['business_manager_id'])) {
                $payload['business_id'] = (string) $row['business_manager_id'];
            }
            if (!empty($row['page_id'])) {
                $payload['page_id'] = (string) $row['page_id'];
            }
            if (!empty($row['instagram_profile_id'])) {
                $payload['instagram_id'] = (string) $row['instagram_profile_id'];
            }
            if (!empty($row['catalog_id'])) {
                $payload['catalog_id'] = (string) $row['catalog_id'];
            }
            if (!empty($row['pixel_id'])) {
                $payload['pixel_id'] = (string) $row['pixel_id'];
            }
            if (!empty($row['ad_account_id'])) {
                $payload['ad_account_id'] = (string) $row['ad_account_id'];
            }
            if (!empty($payload['page_id']) && !empty($payload['catalog_id']) && !empty($payload['pixel_id'])) {
                $payload['status'] = 'connected';
            }
        }

        $this->Store_channel_model->save($this->store->id, 'meta', $payload);
        if ($payload['status'] === 'connected') {
            $this->Store_channel_model->queue_sync_store($this->store, 'meta');
            $this->session->set_flashdata('success', 'Meta connected. Product catalog sync was queued.');
        } else {
            $this->session->set_flashdata('success', 'Meta login successful. Choose your Page, catalog and pixel below.');
        }
        redirect('store/channels');
    }

    protected function meta_save_assets()
    {
        $conn = $this->Store_channel_model->get($this->store->id, 'meta');
        if (!$conn || $conn->access_token === '') {
            $this->session->set_flashdata('error', 'Connect Meta first.');
            redirect('store/channels');
            return;
        }

        $businessId = trim((string) $this->input->post('business_id'));
        $pageId = trim((string) $this->input->post('page_id'));
        $catalogId = trim((string) $this->input->post('catalog_id'));
        $pixelId = trim((string) $this->input->post('pixel_id'));
        $adAccountId = trim((string) $this->input->post('ad_account_id'));

        $pages = $this->meta_commerce->pages($conn->access_token);
        $businesses = $this->meta_commerce->businesses($conn->access_token);
        $pageName = '';
        $pageToken = '';
        $igId = '';
        $igName = '';
        foreach ($pages as $page) {
            if ((string) $page['id'] === $pageId) {
                $pageName = isset($page['name']) ? $page['name'] : '';
                $pageToken = isset($page['access_token']) ? $page['access_token'] : '';
                if (!empty($page['instagram_business_account']['id'])) {
                    $igId = (string) $page['instagram_business_account']['id'];
                    $igName = isset($page['instagram_business_account']['username']) ? $page['instagram_business_account']['username'] : '';
                }
            }
        }
        $businessName = '';
        foreach ($businesses as $biz) {
            if ((string) $biz['id'] === $businessId) {
                $businessName = isset($biz['name']) ? $biz['name'] : '';
            }
        }
        if ($businessId === '' && !empty($businesses[0]['id'])) {
            $businessId = (string) $businesses[0]['id'];
            $businessName = isset($businesses[0]['name']) ? $businesses[0]['name'] : '';
        }

        $catalogName = '';
        if ($catalogId === '__create__' && $businessId !== '') {
            $created = $this->meta_commerce->create_catalog($businessId, $this->store->name . ' Catalog', $conn->access_token);
            if (!$created) {
                $this->session->set_flashdata('error', $this->meta_commerce->last_error() ?: 'Could not create a Meta catalog.');
                redirect('store/channels');
                return;
            }
            $catalogId = (string) $created['id'];
            $catalogName = $this->store->name . ' Catalog';
        } else {
            foreach ($this->meta_commerce->catalogs($businessId, $conn->access_token) as $cat) {
                if ((string) $cat['id'] === $catalogId) {
                    $catalogName = isset($cat['name']) ? $cat['name'] : '';
                }
            }
        }

        $pixelName = '';
        if ($pixelId === '__create__' && $businessId !== '') {
            $created = $this->meta_commerce->create_pixel($businessId, $this->store->name . ' Pixel', $conn->access_token);
            if (!$created) {
                $this->session->set_flashdata('error', $this->meta_commerce->last_error() ?: 'Could not create a Meta pixel.');
                redirect('store/channels');
                return;
            }
            $pixelId = (string) $created['id'];
            $pixelName = $this->store->name . ' Pixel';
        } else {
            foreach ($this->meta_commerce->pixels($businessId, $conn->access_token) as $pixel) {
                if ((string) $pixel['id'] === $pixelId) {
                    $pixelName = isset($pixel['name']) ? $pixel['name'] : '';
                }
            }
        }

        if ($pageId === '' || $catalogId === '' || $pixelId === '') {
            $this->session->set_flashdata('error', 'Select a Facebook Page, catalog and pixel.');
            redirect('store/channels');
            return;
        }
        if ($pageName === '' && $pageId !== '') {
            $this->session->set_flashdata('error', 'That Facebook Page is not available on the connected account.');
            redirect('store/channels');
            return;
        }
        if ($catalogId !== '__create__' && $catalogName === '' && $catalogId !== $conn->catalog_id) {
            $this->session->set_flashdata('error', 'That Meta catalog is not available on the connected account.');
            redirect('store/channels');
            return;
        }
        if ($pixelId !== '__create__' && $pixelName === '' && $pixelId !== $conn->pixel_id) {
            $this->session->set_flashdata('error', 'That Meta pixel is not available on the connected account.');
            redirect('store/channels');
            return;
        }

        $this->Store_channel_model->save($this->store->id, 'meta', array(
            'status' => 'connected',
            'business_id' => $businessId,
            'business_name' => $businessName,
            'page_id' => $pageId,
            'page_name' => $pageName,
            'page_token' => $pageToken,
            'instagram_id' => $igId,
            'instagram_username' => $igName,
            'catalog_id' => $catalogId,
            'catalog_name' => $catalogName,
            'pixel_id' => $pixelId,
            'pixel_name' => $pixelName,
            'ad_account_id' => $adAccountId,
            'last_error' => '',
        ));
        $this->Store_channel_model->queue_sync_store($this->store, 'meta');
        $this->session->set_flashdata('success', 'Meta shop assets saved. Product catalog sync was queued.');
        redirect('store/channels');
    }

    protected function tiktok_connect()
    {
        if (!$this->tiktok_commerce->configured()) {
            $this->session->set_flashdata('error', 'TikTok is not configured by the platform admin yet.');
            redirect('store/channels');
            return;
        }
        $state = $this->Store_channel_model->make_state($this->store->id, 'tiktok');
        $this->session->set_userdata('channel_oauth_state', $state);
        redirect($this->tiktok_commerce->oauth_url($state));
    }

    protected function tiktok_callback()
    {
        $this->requireAuth();
        $this->requirePermission('apps');
        $error = $this->input->get('error') ?: $this->input->get('error_message');
        if ($error) {
            $this->session->set_flashdata('error', $this->oauth_error_message($error));
            redirect('store/channels');
            return;
        }
        $state = $this->Store_channel_model->read_state($this->input->get('state'), 'tiktok', $this->store->id);
        if (!$state || (int) $state['s'] !== (int) $this->store->id || $state['p'] !== 'tiktok') {
            $this->session->set_flashdata('error', 'TikTok login expired. Please connect again.');
            redirect('store/channels');
            return;
        }
        $code = trim((string) ($this->input->get('auth_code') ?: $this->input->get('code')));
        if ($code === '') {
            $this->session->set_flashdata('error', 'TikTok did not return a login code.');
            redirect('store/channels');
            return;
        }
        $token = $this->tiktok_commerce->exchange_code($code);
        if (!$token) {
            $this->session->set_flashdata('error', $this->tiktok_commerce->last_error());
            redirect('store/channels');
            return;
        }
        $advertiserId = !empty($token['advertiser_ids'][0]) ? (string) $token['advertiser_ids'][0] : '';
        $this->Store_channel_model->save($this->store->id, 'tiktok', array(
            'status' => $advertiserId !== '' ? 'needs_assets' : 'needs_assets',
            'access_token' => $token['access_token'],
            'refresh_token' => $token['refresh_token'],
            'token_expires_at' => date('Y-m-d H:i:s', time() + (int) $token['expires_in']),
            'advertiser_id' => $advertiserId,
            'connected_at' => date('Y-m-d H:i:s'),
            'last_error' => '',
            'extra' => array('advertiser_ids' => $token['advertiser_ids']),
        ));
        $this->session->set_flashdata('success', 'TikTok login successful. Choose your advertiser, catalog and pixel below.');
        redirect('store/channels');
    }

    protected function tiktok_save_assets()
    {
        $conn = $this->Store_channel_model->get($this->store->id, 'tiktok');
        if (!$conn || $conn->access_token === '') {
            $this->session->set_flashdata('error', 'Connect TikTok first.');
            redirect('store/channels');
            return;
        }
        $advertiserId = trim((string) $this->input->post('advertiser_id'));
        $catalogId = trim((string) $this->input->post('catalog_id'));
        $pixelId = trim((string) $this->input->post('pixel_id'));
        if ($advertiserId === '') {
            $this->session->set_flashdata('error', 'Select a TikTok advertiser account.');
            redirect('store/channels');
            return;
        }
        if ($advertiserName === '' && $advertiserId !== $conn->advertiser_id) {
            $found = false;
            if (!empty($conn->extra['advertiser_ids']) && in_array($advertiserId, $conn->extra['advertiser_ids'], false)) {
                $found = true;
                $advertiserName = $advertiserId;
            }
            if (!$found) {
                $this->session->set_flashdata('error', 'That TikTok advertiser is not available on the connected account.');
                redirect('store/channels');
                return;
            }
        }
        $advertisers = $this->tiktok_commerce->advertisers($conn->access_token);
        $advertiserName = '';
        foreach ($advertisers as $row) {
            $id = isset($row['advertiser_id']) ? (string) $row['advertiser_id'] : '';
            if ($id === $advertiserId) {
                $advertiserName = isset($row['advertiser_name']) ? $row['advertiser_name'] : $id;
            }
        }
        $catalogName = '';
        if ($catalogId === '__create__') {
            $created = $this->tiktok_commerce->create_catalog($advertiserId, $this->store->name . ' Catalog', $conn->access_token);
            if ($created) {
                $catalogId = (string) $created['catalog_id'];
                $catalogName = $this->store->name . ' Catalog';
            } else {
                $catalogId = '';
            }
        } else {
            foreach ($this->tiktok_commerce->catalogs($advertiserId, $conn->access_token) as $cat) {
                $id = isset($cat['catalog_id']) ? (string) $cat['catalog_id'] : (isset($cat['id']) ? (string) $cat['id'] : '');
                if ($id === $catalogId) {
                    $catalogName = isset($cat['catalog_name']) ? $cat['catalog_name'] : (isset($cat['name']) ? $cat['name'] : '');
                }
            }
        }
        $pixelName = '';
        foreach ($this->tiktok_commerce->pixels($advertiserId, $conn->access_token) as $pixel) {
            $id = isset($pixel['pixel_code']) ? (string) $pixel['pixel_code'] : (isset($pixel['pixel_id']) ? (string) $pixel['pixel_id'] : '');
            if ($id === $pixelId) {
                $pixelName = isset($pixel['pixel_name']) ? $pixel['pixel_name'] : (isset($pixel['name']) ? $pixel['name'] : '');
            }
        }
        if ($pixelId === '') {
            $this->session->set_flashdata('error', 'Select a TikTok pixel.');
            redirect('store/channels');
            return;
        }
        if ($pixelName === '' && $pixelId !== $conn->pixel_id) {
            $this->session->set_flashdata('error', 'That TikTok pixel is not available on the connected account.');
            redirect('store/channels');
            return;
        }
        $this->Store_channel_model->save($this->store->id, 'tiktok', array(
            'status' => 'connected',
            'advertiser_id' => $advertiserId,
            'advertiser_name' => $advertiserName,
            'catalog_id' => $catalogId,
            'catalog_name' => $catalogName,
            'pixel_id' => $pixelId,
            'pixel_name' => $pixelName,
            'last_error' => '',
        ));
        if ($catalogId !== '') {
            $this->Store_channel_model->queue_sync_store($this->store, 'tiktok');
        }
        $this->session->set_flashdata('success', 'TikTok connected. Tracking is live' . ($catalogId !== '' ? ' and catalog sync was queued.' : '.'));
        redirect('store/channels');
    }

    protected function run_sync($platform)
    {
        if (strtoupper((string) $this->input->method()) !== 'POST') {
            redirect('store/channels');
            return;
        }
        $conn = $this->Store_channel_model->get($this->store->id, $platform);
        if (!$conn || $conn->status !== 'connected') {
            $this->session->set_flashdata('error', 'Connect the channel before syncing products.');
            redirect('store/channels');
            return;
        }
        $this->Store_channel_model->queue_sync_store($this->store, $platform);
        $this->session->set_flashdata('success', ucfirst($platform) . ' catalog sync was queued.');
        redirect('store/channels');
    }

    protected function oauth_error_message($error)
    {
        $error = (string) $error;
        if (stripos($error, 'denied') !== false || stripos($error, 'cancel') !== false) {
            return 'Login was cancelled or permission was denied. Please connect again and approve the requested access.';
        }
        if (stripos($error, 'expired') !== false) {
            return 'The login session expired. Please connect again.';
        }
        return 'Login failed: ' . $error;
    }

    protected function meta_asset_options($conn)
    {
        $empty = array(
            'pages' => array(),
            'businesses' => array(),
            'catalogs' => array(),
            'pixels' => array(),
            'ad_accounts' => array(),
        );
        if (!$conn || $conn->access_token === '' || $conn->status === 'disconnected' || $conn->status === 'token_expired') {
            return $empty;
        }
        $pages = $this->meta_commerce->pages($conn->access_token);
        $businesses = $this->meta_commerce->businesses($conn->access_token);
        $businessId = $conn->business_id;
        if ($businessId === '' && !empty($businesses[0]['id'])) {
            $businessId = (string) $businesses[0]['id'];
        }
        return array(
            'pages' => $pages,
            'businesses' => $businesses,
            'catalogs' => $this->meta_commerce->catalogs($businessId, $conn->access_token),
            'pixels' => $this->meta_commerce->pixels($businessId, $conn->access_token),
            'ad_accounts' => $this->meta_commerce->ad_accounts($businessId, $conn->access_token),
        );
    }

    protected function tiktok_asset_options($conn)
    {
        $empty = array(
            'advertisers' => array(),
            'catalogs' => array(),
            'pixels' => array(),
        );
        if (!$conn || $conn->access_token === '' || $conn->status === 'disconnected' || $conn->status === 'token_expired') {
            return $empty;
        }
        $advertisers = $this->tiktok_commerce->advertisers($conn->access_token);
        if (empty($advertisers) && !empty($conn->extra['advertiser_ids'])) {
            foreach ($conn->extra['advertiser_ids'] as $id) {
                $advertisers[] = array('advertiser_id' => (string) $id, 'advertiser_name' => (string) $id);
            }
        }
        $advertiserId = $conn->advertiser_id;
        if ($advertiserId === '' && !empty($advertisers[0]['advertiser_id'])) {
            $advertiserId = (string) $advertisers[0]['advertiser_id'];
        }
        return array(
            'advertisers' => $advertisers,
            'catalogs' => $advertiserId !== '' ? $this->tiktok_commerce->catalogs($advertiserId, $conn->access_token) : array(),
            'pixels' => $advertiserId !== '' ? $this->tiktok_commerce->pixels($advertiserId, $conn->access_token) : array(),
        );
    }
}

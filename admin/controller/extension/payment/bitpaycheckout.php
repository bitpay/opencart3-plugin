<?php
class ControllerExtensionPaymentBitpaycheckout extends Controller {
	private $error = array();
    public function install() {
      
        $this->load->model('extension/payment/bitpaycheckout');
        $this->model_extension_payment_bitpaycheckout->install();
    }
	public function index() {
		$this->install();
        #error_log(print_r($this->request->post,true));
		$this->load->language('extension/payment/bitpaycheckout');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('payment_bitpaycheckout', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/payment/bitpaycheckout', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/payment/bitpaycheckout', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true);

		if (isset($this->request->post['payment_bitpaycheckout_total'])) {
			$data['payment_bitpaycheckout_total'] = $this->request->post['payment_bitpaycheckout_total'];
		} else {
			$data['payment_bitpaycheckout_total'] = $this->config->get('payment_bitpaycheckout_total');
		}
		
		if (isset($this->request->post['payment_bitpaycheckout_env'])) {
			$data['payment_bitpaycheckout_env'] = $this->request->post['payment_bitpaycheckout_env'];
		} 	else {
			$data['payment_bitpaycheckout_env'] = $this->config->get('payment_bitpaycheckout_env');
		}

	
		if (isset($this->request->post['payment_bitpaycheckout_prod_token'])) {
			$data['payment_bitpaycheckout_prod_token'] = $this->request->post['payment_bitpaycheckout_prod_token'];
		} else {
			$data['payment_bitpaycheckout_prod_token'] = $this->config->get('payment_bitpaycheckout_prod_token');
		}


		if (isset($this->request->post['payment_bitpaycheckout_dev_token'])) {
			$data['payment_bitpaycheckout_dev_token'] = $this->request->post['payment_bitpaycheckout_dev_token'];
		} else {
			$data['payment_bitpaycheckout_dev_token'] = $this->config->get('payment_bitpaycheckout_dev_token');
		}

		if (isset($this->request->post['payment_bitpaycheckout_flow'])) {
			$data['payment_bitpaycheckout_flow'] = $this->request->post['payment_bitpaycheckout_flow'];
		} else {
			$data['payment_bitpaycheckout_flow'] = $this->config->get('payment_bitpaycheckout_flow');
		}


		if (isset($this->request->post['payment_bitpaycheckout_capture_email'])) {
			$data['payment_bitpaycheckout_capture_email'] = $this->request->post['payment_bitpaycheckout_capture_email'];
		} else {
			$data['payment_bitpaycheckout_capture_email'] = $this->config->get('payment_bitpaycheckout_capture_email');
		}


		if (isset($this->request->post['payment_bitpaycheckout_order_status_id'])) {
			$data['payment_bitpaycheckout_order_status_id'] = $this->request->post['payment_bitpaycheckout_order_status_id'];
		} else {
			$data['payment_bitpaycheckout_order_status_id'] = $this->config->get('payment_bitpaycheckout_order_status_id');
		}

		$this->load->model('localisation/order_status');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		if (isset($this->request->post['payment_bitpaycheckout_geo_zone_id'])) {
			$data['payment_bitpaycheckout_geo_zone_id'] = $this->request->post['payment_bitpaycheckout_geo_zone_id'];
		} else {
			$data['payment_bitpaycheckout_geo_zone_id'] = $this->config->get('payment_bitpaycheckout_geo_zone_id');
		}

		$this->load->model('localisation/geo_zone');

		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		if (isset($this->request->post['payment_bitpaycheckout_status'])) {
			$data['payment_bitpaycheckout_status'] = $this->request->post['payment_bitpaycheckout_status'];
		} else {
			$data['payment_bitpaycheckout_status'] = $this->config->get('payment_bitpaycheckout_status');
		}

		if (isset($this->request->post['payment_bitpaycheckout_sort_order'])) {
			$data['payment_bitpaycheckout_sort_order'] = $this->request->post['payment_bitpaycheckout_sort_order'];
		} else {
			$data['payment_bitpaycheckout_sort_order'] = $this->config->get('payment_bitpaycheckout_sort_order');
		}

		#echo '<pre>';
		#print_r($data);
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/payment/bitpaycheckout', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/payment/bitpaycheckout')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}

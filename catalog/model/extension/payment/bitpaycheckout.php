<?php
#Bitpay
class ModelExtensionPaymentBitpaycheckout extends Model {
	public function getMethod($address, $total) {
		$this->load->language('extension/payment/bitpaycheckout');

		if ($total <= 0.00) {
			$status = true;
		} else {
			$status = false;
        }
        $status = true;

		$method_data = array();

		if ($status) {
			$method_data = array(
				'code'       => 'bitpaycheckout',
				'title'      => $this->language->get('text_title'),
				'terms'      => '',
				'sort_order' => $this->config->get('payment_bitpaycheckout_sort_order')
			);
		}

		return $method_data;
	}
}

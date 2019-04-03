<?php
class ModelExtensionPaymentBitpaycheckout extends Model {

	public function install() {
        $charset_collate = "ENGINE=MyISAM DEFAULT CHARSET=utf8";
        $table_name = DB_PREFIX.'bitpay_checkout_transactions';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name(
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `order_id` int(11) NOT NULL,
            `transaction_id` varchar(255) NOT NULL,
            `transaction_status` varchar(50) NOT NULL DEFAULT 'new',
            `date_added` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
            ) $charset_collate;";
    
		$this->db->query($sql);

		
	}

	public function uninstall() {
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "bitpay_checkout_transactions`;");
	}

	
}

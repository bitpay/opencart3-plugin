<?php
#Bitpay
class ControllerExtensionPaymentBitpaycheckout extends Controller
{
    public function index()
    {
        $data['continue'] = $this->url->link('checkout/success');
        return $this->load->view('extension/payment/bitpaycheckout', $data);
    }

    public function BPC_OC_Version()
    {
        $name = 'BitPay_Checkout';
        $platform = 'OpenCart';
        $version = "3.0.0.2";
        return $name . '_' . $platform . '_' . $version;
    }
    public function BPC_getBitPayDashboardLink($invoiceID)
    { //dev or prod token
        $env = intval($this->config->get('payment_bitpaycheckout_env'));
        if ($env != 1) {
            $env = 'test';
        } else {
            $env = 'prod';
        }

        switch ($env) {
            case 'test':
            default:
                return '//test.bitpay.com/dashboard/payments/' . $invoiceID;
                break;
            case 'production':
                return '//bitpay.com/dashboard/payments/' . $invoiceID;
                break;
        }
    }
    public function ipnUpdate($order_id, $order_invoice, $event)
    {
        $table_name = DB_PREFIX . 'bitpay_checkout_transactions';
        $this->load->model('checkout/order');
        switch ($event) {
            #complete, update invoice table to Paid
            case 'invoice_confirmed':
                $note = 'BitPay Invoice ID: <a target = "_blank" href = "' . $this->BPC_getBitPayDashboardLink($order_invoice) . '">' . $order_invoice . '</a> processing has been completed.';
                $order = $this->model_checkout_order->getOrder($order_id); // use the desired $orderId here
                $this->model_checkout_order->addOrderHistory($order_id, 5, $note);

                $sql = "UPDATE $table_name SET transaction_status = '$event' WHERE order_id = '$order_id' AND transaction_id = '$order_invoice'";
                $this->db->query($sql);

                break;

            #processing - put in Payment Pending
            case 'invoice_paidInFull':
                $note = 'BitPay Invoice ID: <a target = "_blank" href = "' . $this->BPC_getBitPayDashboardLink($order_invoice) . '">' . $order_invoice . '</a> is processing.';

                $order = $this->model_checkout_order->getOrder($order_id); // use the desired $orderId here
                $this->model_checkout_order->addOrderHistory($order_id, 1, $note);

                $sql = "UPDATE $table_name SET transaction_status = '$event' WHERE order_id = '$order_id' AND transaction_id = '$order_invoice'";
                $this->db->query($sql);
                break;

            #confirmation error - put in Payment Pending
            case 'invoice_failedToConfirm':
                $note = 'BitPay Invoice ID: <a target = "_blank" href = "' . $this->BPC_getBitPayDashboardLink($order_invoice) . '">' . $order_invoice . '</a> has become invalid because of network congestion.  Order will automatically update when the status changes.';
                $order = $this->model_checkout_order->getOrder($order_id); // use the desired $orderId here
                $this->model_checkout_order->addOrderHistory($order_id, 1, $note);

                $sql = "UPDATE $table_name SET transaction_status = '$event' WHERE order_id = '$order_id' AND transaction_id = '$order_invoice'";
                $this->db->query($sql);
                break;

            #expired, remove from transaction table, wont be in invoice table
            case 'invoice_expired':
                #delete any orphans
                $this->model_checkout_order->deleteOrder($order_id);

                #delete from transaction_list
                $table_name = DB_PREFIX . 'bitpay_checkout_transactions';
                $sql = "DELETE FROM  $table_name WHERE order_id = $order_id AND transaction_id = '$order_invoice'";
                $this->db->query($sql);

                break;

            #update both table to refunded
            case 'invoice_refundComplete':
                $note = 'BitPay Invoice ID: <a target = "_blank" href = "' . $this->BPC_getBitPayDashboardLink($order_invoice) . '">' . $order_invoice . ' </a> has been refunded.';

                $order = $this->model_checkout_order->getOrder($order_id); // use the desired $orderId here
                $this->model_checkout_order->addOrderHistory($order_id, 11, $note);

                $sql = "UPDATE $table_name SET transaction_status = '$event' WHERE order_id = '$order_id' AND transaction_id = '$order_invoice'";
                $this->db->query($sql);
                break;
        }
        http_response_code(200);
    }

    public function confirm()
    {
        spl_autoload_register(function ($class) {
            #include __DIR__ . '/BitPayLib/' . $class . '.php';
            if (strpos($class, 'BPC_') !== false):
                if (!class_exists('BitPayLib/' . $class, false)):
                    #doesnt exist so include it
                    include __DIR__ . '/BitPayLib/' . $class . '.php';
                endif;
            endif;
        });
        $this->load->model('setting/setting');

        #ipn updates
        if ($_GET['action'] == 'ipn') {
            #delete from OC
            $all_data = json_decode(file_get_contents("php://input"), true);
            #
            $data = $all_data['data'];
            $event = $all_data['event'];
            $orderid = $data['orderId'];

            $order_status = $data['status'];
            $order_invoice = $data['id'];

            $price = $data['price'];

            $table_name = DB_PREFIX . 'bitpay_checkout_transactions';
            $sql = "SELECT * FROM $table_name WHERE order_id = '$orderid' AND transaction_id = '$order_invoice' LIMIT 1";
            $result = $this->db->query($sql);

            if ($result->num_rows > 0):
                #found in the table, now update
                $this->ipnUpdate($orderid, $order_invoice, $event['name']);
            endif;

            die();
        }

        if ($this->session->data['payment_method']['code'] == 'bitpaycheckout') {
            $this->load->model('checkout/order');

            $order_id = $this->session->data['order_id'];
            #delete the order
            if ($_GET['action'] == 'd') {
                #delete from OC
                $this->model_checkout_order->deleteOrder($order_id);
                #delete from transaction_list
                $table_name = DB_PREFIX . 'bitpay_checkout_transactions';
                $sql = "DELETE FROM  $table_name WHERE order_id = $order_id";
                $this->db->query($sql);
                echo true;
                die();
            }
            #change transaction status in bitpay_transaction
            if ($_GET['action'] == 'c') {
                #update transaction_list
                $table_name = DB_PREFIX . 'bitpay_checkout_transactions';
                $sql = "UPDATE $table_name SET transaction_status = 'paid' WHERE order_id = $order_id";
                $this->db->query($sql);
                echo true;
                die();
            }

            $order = $this->model_checkout_order->getOrder($order_id); // use the desired $orderId here
            $this->model_checkout_order->addOrderHistory($order_id, $this->config->get('payment_bitpaycheckout_order_status_id'));

            #BitPay Library Stuff
            $env = intval($this->config->get('payment_bitpaycheckout_env'));
            if ($env != 1) {
                $env = 'test';
            } else {
                $env = 'prod';
            }
            switch ($env) {
                case 'test':
                    $bitpay_checkout_token = $this->config->get('payment_bitpaycheckout_dev_token');
                    break;
                case 'prod':
                    $bitpay_checkout_token = $this->config->get('payment_bitpaycheckout_prod_token');
                    break;
            }
            $config = new BPC_Configuration($bitpay_checkout_token, $env);
            $use_modal = intval($this->config->get('payment_bitpaycheckout_flow')); #1 = modal
            $capture_email = intval($this->config->get('payment_bitpaycheckout_capture_email')); #1 = capture

            #get the order info

            $params = new stdClass();
            $params->extension_version = $this->BPC_OC_Version();
            $params->price = $order['total'];
            $params->currency = $order['currency_code'];
            $params->orderId = trim($order_id);
            $hash_key = $config->BPC_generateHash($params->orderId);

            #user email
            if ($capture_email == 1 && !empty($this->customer->getEmail())) {
                $buyerInfo = new stdClass();
                $buyerInfo->name = $this->customer->getFirstName() . ' ' . $this->customer->getLastName();
                $buyerInfo->email = $this->customer->getEmail();
                $params->buyer = $buyerInfo;

            }
            $params->redirectURL = HTTPS_SERVER . 'index.php?route=checkout/success&order_id=' . $order_id;
            $params->notificationURL = HTTPS_SERVER . 'index.php?route=extension/payment/bitpaycheckout/confirm&action=ipn';
            $params->extendedNotifications = true;
            $params->transactionSpeed = 'medium';
            $params->acceptanceWindow = 1200000;

            $item = new BPC_Item($config, $params);
            $invoice = new BPC_Invoice($item);
            //this creates the invoice with all of the config params from the item
            $invoice->BPC_createInvoice();
            $invoiceData = json_decode($invoice->BPC_getInvoiceData());
            //now we have to append the invoice transaction id for the callback verification
            $invoiceID = $invoiceData->data->id;
            $response = new stdClass();
            $response->use_modal = $use_modal;
            $response->env = $env;
            $response->invoiceID = $invoiceID;
            $response->redirectURL = $params->redirectURL;
            $response->order_id = $params->orderId;
            $response->invoiceredirectURL = $invoice->BPC_getInvoiceURL();

            #insert into the database
            if ($invoiceID != '') {
                $table_name = DB_PREFIX . 'bitpay_checkout_transactions';
                $sql = "INSERT INTO $table_name (order_id,transaction_id,transaction_status)
                VALUES ('$params->orderId','$invoiceID','new')";
                $this->db->query($sql);
            }
            echo (json_encode($response));
        }
    }
}

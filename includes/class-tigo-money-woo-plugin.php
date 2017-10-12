<?php
/**
 * Created by PhpStorm.
 * User: smp
 * Date: 8/10/17
 * Time: 11:11 AM
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
class Tigo_Money_Woo_Plugin
{
	/**
	 * Filepath of main plugin file.
	 *
	 * @var string
	 */
	public $file;
	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	public $version;
	/**
	 * Absolute plugin path.
	 *
	 * @var string
	 */
	public $plugin_path;
	/**
	 * Absolute plugin URL.
	 *
	 * @var string
	 */
	public $plugin_url;
	/**
	 * Absolute path to plugin includes dir.
	 *
	 * @var string
	 */
	public $includes_path;
	/**
	 * Flag to indicate the plugin has been boostrapped.
	 *
	 * @var bool
	 */
	private $_bootstrapped = false;
	/**
	 * Instance of WC_Gateway_PCW_Settings.
	 *
	 * @var WC_Gateway_PCW_Settings
	 */
	public $settings;
	public function __construct($file, $version)
	{
		$this->file = $file;
		$this->version = $version;
		// Path.
		$this->plugin_path   = trailingslashit( plugin_dir_path( $this->file ) );
		$this->plugin_url    = trailingslashit( plugin_dir_url( $this->file ) );
		$this->includes_path = $this->plugin_path . trailingslashit( 'includes' );
	}

	public function tigo_run()
	{
		try{
			if ($this->_bootstrapped){
				throw new Exception( __( 'Form Print Pay can only be called once', 'doliwoo' ));
			}
			$this->_run();
			$this->_bootstrapped = true;
		}catch (Exception $e){
			if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
				do_action('notices_action_tag_tigo_woo', $e->getMessage());
			}
		}
	}

	protected function _run()
	{
		require_once ($this->includes_path . 'class-wc-payment-tigo-money-woo.php');
		add_filter( 'plugin_action_links_' . plugin_basename( $this->file), array( $this, 'plugin_action_links' ) );
		add_filter( 'woocommerce_payment_gateways', array($this, 'woocommerce_tigo_money_add_gateway'));
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'wp', array($this, 'return_params_tigo_money'));
		add_action('wp_ajax_tigo_money_form',array($this,'tigo_money_form_suscribir'));
		add_action('wp_ajax_nopriv_tigo_money_form',array($this,'tigo_money_form_suscribir'));
		$this->_load_handlers();
	}

	protected function _load_handlers()
	{
		require_once ($this->includes_path . 'class-tigo-money-woo-curl.php');
		$this->cURL = new Tigo_Money_Woo_Curl();
	}

	public function plugin_action_links($links)
	{
		$plugin_links = array();
		$plugin_links[] = '<a href="'.admin_url( 'admin.php?page=wc-settings&tab=checkout&section=tigo_money').'">' . esc_html__( 'Settings', 'tigo-money-woo' ) . '</a>';
		$plugin_links[] = '<a href="https://saulmoralespa.github.io/tigo-money-woo/">' . esc_html__( 'Documentation', 'tigo-money-woo' ) . '</a>';
		return array_merge( $plugin_links, $links );
	}

	public function woocommerce_tigo_money_add_gateway($methods)
	{
		$methods[] = 'WC_Payment_Tigo_Money_Woo';
		return $methods;
	}

	public function enqueue_scripts()
	{
		wp_enqueue_script( 'tigo-money-woo', $this->plugin_url . 'assets/js/tigo-money-woo.js', array( 'jquery' ), $this->version, true );
		wp_localize_script( 'tigo-money-woo', 'tigo_money_woo', array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'loading' => __('Consulting Tigo Money and generating secure url','tigo-money-woo'),
			'message_redirect' => __('Redirecting to Tigo Money','tigo-money-woo')
		) );
		wp_enqueue_style('frontend-tigo-money-woo', $this->plugin_url . 'assets/css/tigo-money-woo.css', array(), $this->version, null);
	}

	public function return_params_tigo_money()
	{
		if (empty($_REQUEST['transactionStatus']) || empty($_REQUEST['merchantTransactionId'])  || empty($_REQUEST['transactionCode'])){
			return;
		}

		$request = $_SERVER['REQUEST_METHOD'];
		$merchantTransactionId = $_REQUEST['merchantTransactionId'];
		$order_id = explode('id',$merchantTransactionId);
		$order_id =(int)$order_id[0];
		$order = new WC_Order($order_id);
		$WC_Tigo_Money_Woo = new WC_Payment_Tigo_Money_Woo();
		$logger = new WC_Logger();

		$message = '';
		$messageClass = '';

		if ( $order->get_status() != 'completed' &&  $order->get_status() != 'processing'){
			$api_key = $WC_Tigo_Money_Woo->get_option('api_key');
			$api_secret = $WC_Tigo_Money_Woo->get_option('api_secret');
			$access = $api_key . ":" . $api_secret;
			$token = tigo_money_woo()->cURL->execute($WC_Tigo_Money_Woo->createUrl(true),'grant_type=client_credentials',$access);
			$token = json_decode($token);
			if (!isset($token->accessToken)){
				$print = print_r($token,true);
				$logger->add('tigo_money',__("return_params_tigo_money: $print",'tigo-money-woo'));
				return;
			}
			$authorization = "Authorization: Bearer $token->accessToken";
			$mfsTransactionId = isset($_REQUEST['mfsTransactionId']) ? $_REQUEST['mfsTransactionId'] : get_bloginfo('name');
			$status = tigo_money_woo()->cURL->execute($WC_Tigo_Money_Woo->createUrl(true,true)."$mfsTransactionId/$merchantTransactionId",'GET',$authorization);
			$status = json_decode($status);
			if (!isset($status->Transaction->status)){
				$print = print_r($token,true);
				$logger->add('tigo_money',__("return_params_tigo_money TransactionId: $print",'tigo-money-woo'));
				return;
			}

			switch ($status->Transaction->status){
				case 'success':
					$order->payment_complete($merchantTransactionId);
					$message = __('Successful payment','tigo-money-woo');
					$messageClass = 'woocommerce-message';
					$order->add_order_note(__('Successful payment','tigo-money-woo'));
					break;
				case 'fail':
				case 'reverted':
				$message = __('Payment failed','tigo-money-woo');
				$messageClass = 'woocommerce-error';
				$order->update_status('failed');
				$order->add_order_note(__('Payment failed','tigo-money-woo'));
				$WC_Tigo_Money_Woo->restore_order_stock($order_id);
					break;
				case 'initiated':
					$message = __('Payment in the initiated state','');
					$messageClass = 'woocommerce-info';
					$order->update_status('on-hold');
					$order->add_order_note(__('Payment in the initiated state','tigo-money-woo'));
					break;
			}
		}elseif($order->get_status() == 'completed'){
			$message = __('Successful payment','tigo-money-woo');
			$messageClass = 'woocommerce-message';
		}elseif ($order->get_status() == 'processing'){
			$message = __('Payment in the initiated state','');
			$messageClass = 'woocommerce-info';
		}

		if ($request == 'GET'){
			$redirect_url = add_query_arg( array('msg'=> urlencode($message), 'type'=> $messageClass), $order->get_checkout_order_received_url() );
			wp_redirect( $redirect_url );
		}

	}

	public function tigo_money_form_suscribir()
	{

		$WC_Tigo_Money_Woo = new WC_Payment_Tigo_Money_Woo();
		$api_key = $WC_Tigo_Money_Woo->get_option('api_key');
		$api_secret = $WC_Tigo_Money_Woo->get_option('api_secret');
		$order_id =(int)$_POST['id_order_tigo_money'];
		$order = new WC_Order($order_id);
		$access = $api_key . ":" . $api_secret;
		$token = tigo_money_woo()->cURL->execute($WC_Tigo_Money_Woo->createUrl(true),'grant_type=client_credentials',$access);
		$token = json_decode($token);
		if(isset($token->accessToken)){
			$transactionid = "{$order_id}id".time();
			$authorization = "Authorization: Bearer $token->accessToken";
			$total=round($order->get_total(),2);
			$array = array('Subscriber' => array('account' => $_POST['number_subscriber_tigo_money'], 'countryCode' => '595', 'country' => 'PRY', 'emailId' => $order->get_billing_email()), 'MasterMerchant' => array('account' => $WC_Tigo_Money_Woo->get_option('account'), 'pin' => $WC_Tigo_Money_Woo->get_option('pin'), 'id' => get_bloginfo('name')), 'redirectUri' => home_url(), 'callbackUri' => home_url(), 'language' => 'spa', 'OriginPayment' => array('amount' => $total, 'currencyCode' => 'PYG', 'tax' => '0.00', 'fee' => '0.00'), 'exchangeRate' => '1', 'LocalPayment' => array('amount' => $total, 'currencyCode' => 'PYG'), 'merchantTransactionId' => $transactionid);
			$json = json_encode($array);
			$redirectUrl = tigo_money_woo()->cURL->execute($WC_Tigo_Money_Woo->createUrl(),$json,$authorization);
			$redirectUrl = json_decode($redirectUrl);
			if (isset($redirectUrl->redirectUrl)){
				die(json_encode(array('status' => true,'url' => $redirectUrl->redirectUrl)));
			}else{
				die(json_encode(array('status' => false,'message' => $redirectUrl->error_description)));
			}
		}elseif(isset($token->error_description)){
			die(json_encode(array('status' => false, 'message' => $token->error_description)));
		}elseif(isset($token->fault->faultstring)){
			die(json_encode(array('status' => false, 'message' => $token->fault->faultstring)));
		}

	}
}
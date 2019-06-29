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

	/**
	 * @var WC_Logger
	 */
	public $logger;

	public function __construct($file, $version)
	{
		$this->file = $file;
		$this->version = $version;
		// Path.
		$this->plugin_path   = trailingslashit( plugin_dir_path( $this->file ) );
		$this->plugin_url    = trailingslashit( plugin_dir_url( $this->file ) );
		$this->includes_path = $this->plugin_path . trailingslashit( 'includes' );
		$this->logger = new WC_Logger();
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

        if(is_checkout()){
            wp_enqueue_script( 'tigo-money-woo', $this->plugin_url . 'assets/js/tigo-money-woo.js', array( 'jquery' ), $this->version, true );
            wp_enqueue_script( 'tigo-money-sweet-alert', $this->plugin_url . 'assets/js/sweetalert2.js', array( 'jquery' ), $this->version, true );
            wp_localize_script( 'tigo-money-woo', 'tigo_money_woo', array(
                'ajaxurl' => admin_url( 'admin-ajax.php' ),
                'loading' => __('Consulting Tigo Money and generating secure url','tigo-money-woo'),
                'message_redirect' => __('Redirecting to Tigo Money','tigo-money-woo')
            ) );
            wp_enqueue_style('frontend-tigo-money-woo', $this->plugin_url . 'assets/css/tigo-money-woo.css', array(), $this->version, null);
        }
	}

	public function return_params_tigo_money()
	{
		if (empty($_REQUEST['transactionStatus']) || empty($_REQUEST['merchantTransactionId'])  || empty($_REQUEST['transactionCode']))
			return;

		$request = $_SERVER['REQUEST_METHOD'];
		$merchantTransactionId = $_REQUEST['merchantTransactionId'];
		if (strpos($merchantTransactionId, 'id') === false)
			die('No param order id');
 		$order_id = explode('id',$merchantTransactionId);
		$order_id =(int)$order_id[0];
		$order = new WC_Order($order_id);
		$WC_Tigo_Money_Woo = new WC_Payment_Tigo_Money_Woo();

		$message = '';
		$messageClass = '';

		if ( $order->get_status() != 'completed' &&  $order->get_status() != 'processing'){
			$api_key = $WC_Tigo_Money_Woo->get_option('api_key');
			$api_secret = $WC_Tigo_Money_Woo->get_option('api_secret');
			$access = $api_key . ":" . $api_secret;
			$access = base64_encode($access);
			$token = wp_safe_remote_post( $WC_Tigo_Money_Woo->createUrl(true), array('headers' => array( 'cache-control' => 'no-cache','content-type'  => 'application/x-www-form-urlencoded', 'authorization' => 'Basic '. $access ),'body' => array( 'grant_type' => 'client_credentials')));

			if ( is_wp_error( $token ) ) {
				$this->logger->add( 'tigo_money', __( 'We are currently experiencing problems trying to connect to this payment gateway. Sorry for the inconvenience.', 'tigo-money-woo' ) );
				return;
			}

			if ( $token['response']['code'] != 200 ) {
				$this->logger->add( 'tigo_money', __( 'Oops! Something Bad Happen, check the Consumer Key and Consumer Secret and try Again.', 'tigo-money-woo' ) );
				return;
			}

			$token = wp_remote_retrieve_body( $token );
			$token = json_decode($token);
			$mfsTransactionId = get_bloginfo('name');
			$status = wp_safe_remote_get( $WC_Tigo_Money_Woo->createUrl(true,true)."$mfsTransactionId/$merchantTransactionId", array('headers' => array('cache-control' => 'no-cache', 'content-type' => 'application/json','authorization' => 'Bearer '. $token->accessToken ) ));

			if ( is_wp_error( $status ) ) {
				$this->logger->add( 'tigo_money', 'return_params_tigo_money, status:'  .  __( 'We are currently experiencing problems trying to connect to this payment gateway. Sorry for the inconvenience.', 'tigo-money-woo' ) );
				return;
			}

			if ( $status['response']['code'] != 200 ) {
				$this->logger->add( 'tigo_money', __( 'merchantTransactionId o mfsTransactionId could not be found.', 'tigo-money-woo' ) );
				return;
			}

			$status = wp_remote_retrieve_body( $status );
			$status = json_decode($status);
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
					$message = __('Payment in the initiated state','tigo-money-woo');
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

		if ($request === 'GET'){
			$redirect_url = add_query_arg( array('msg'=> urlencode($message), 'type'=> $messageClass), $order->get_checkout_order_received_url() );
			wp_redirect( $redirect_url );
		}

	}

	public function tigo_money_transaction(array $params)
	{

		$WC_Tigo_Money_Woo = new WC_Payment_Tigo_Money_Woo();
		$api_key = $WC_Tigo_Money_Woo->get_option('api_key');
		$api_secret = $WC_Tigo_Money_Woo->get_option('api_secret');
		$environment = $WC_Tigo_Money_Woo->get_option('environment');

		$redirect_test = 'https://test.api.tigo.com/v1/tigo/diagnostics/callback';

        $uri_redirect = $environment === "test" ? $redirect_test : get_bloginfo( 'url' );

		$order_id =(int)$params['id_order'];
		$order = new WC_Order($order_id);
		$access = $api_key . ":" . $api_secret;
		$access = base64_encode($access);
		$token = wp_safe_remote_post( $WC_Tigo_Money_Woo->createUrl(true), array(
		    'headers' => array(
		        'cache-control' => 'no-cache',
                'content-type'  => 'application/x-www-form-urlencoded',
                'authorization' => 'Basic '. $access ),
            'body' => array(
                'grant_type' => 'client_credentials')
        ));

            if ( is_wp_error( $token ) )
			return (wp_json_encode(array(
			    'status' => false,
                'message' => $token->get_error_message()
            )));

		if ( $token['response']['code'] != 200 )
			return array(
			    'status' => false,
                'message' => __('Oops! Something Bad Happen, check the Consumer Key and Consumer Secret and try Again.','tigo-money-woo'));

		$token = wp_remote_retrieve_body( $token );
		$token = json_decode($token);

		if(isset($token->accessToken)){
			$transactionid = "{$order_id}id".time();
            $total = (string)round($order->get_total(),2);

			$array = array(
			    'MasterMerchant' => array(
			    'account' => $WC_Tigo_Money_Woo->get_option('account'),
                'pin' => $WC_Tigo_Money_Woo->get_option('pin'),
                'id' => get_bloginfo('name')),
                'Subscriber' => array(
                    'account' => $params['number_tigo_money'],
                    'countryCode' => '595',
                    'country' => 'PRY',
                    'emailId' => $order->get_billing_email()),
                'redirectUri' => $uri_redirect,
                'callbackUri' => $uri_redirect,
                'language' => 'spa',
                'OriginPayment' => array(
                    'amount' => $total,
                    'currencyCode' => 'PYG',
                    'tax' => '0.00',
                    'fee' => '0.00'),
                'exchangeRate' => '1',
                'LocalPayment' => array(
                    'amount' => $total,
                    'currencyCode' => 'PYG'),
                'merchantTransactionId' => $transactionid);
			$json = json_encode($array);
			$response_authorization_payment_request = wp_safe_remote_post($WC_Tigo_Money_Woo->createUrl(),
				array(
				    'headers' => array(
				        'cache-control' => 'no-cache',
                        'content-type' => 'application/json',
                        'authorization' => 'Bearer '. $token->accessToken ),
                    'body' => $json ));
			$body_payment = wp_remote_retrieve_body( $response_authorization_payment_request );
			$body_payment = json_decode($body_payment);
			if (isset($body_payment->redirectUrl)){
				return array('status' => true,'url' => $body_payment->redirectUrl);
			}else{
				return array('status' => false,'message' => $body_payment->error_description);
			}
		}elseif(isset($token->error_description)){
			return array('status' => false, 'message' => $token->error_description);
		}else{
			return array('status' => false, 'message' => $token->fault->faultstring);
		}
	}
}
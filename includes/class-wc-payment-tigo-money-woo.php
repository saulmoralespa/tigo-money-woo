<?php
/**
 * Created by PhpStorm.
 * User: smp
 * Date: 8/10/17
 * Time: 12:06 PM
 */

class WC_Payment_Tigo_Money_Woo extends WC_Payment_Gateway
{
	public function __construct()
	{
		$this->id = 'tigo_money';
		$this->icon = tigo_money_woo()->plugin_url . 'assets/img/tigo_money.png';
		$this->method_title = __('Tigo Money Woo', 'tigo-money-woo');
		$this->method_description = __('Pago simple de Tigo Money solo para Paraguay.', 'tigo-money-woo');
		$this->description  = $this->get_option( 'description' );
		$this->order_button_text = __('Continue to payment', 'tigo-money-woo');
		$this->has_fields = true;
		$this->supports = array('products');
		$this->init_form_fields();
		$this->init_settings();
		$this->title = $this->get_option('title');
		add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
		add_action('woocommerce_receipt_' . $this->id, array(&$this, 'receipt_page'));
		add_filter('woocommerce_thankyou_order_received_text', array($this, 'order_received_message'), 10, 2 );
	}

	public function init_form_fields()
	{
		wc_enqueue_js( "jQuery( function( $ ) {
	jQuery('form#mainform').submit(function(e){
		var account = jQuery('input#woocommerce_tigo_money_account').val();
		var pin = jQuery('input#woocommerce_tigo_money_pin').val();
		var api_key = jQuery('input#woocommerce_tigo_money_api_key').val();
		var api_secret = jQuery('input#woocommerce_tigo_money_api_secret').val();
		if (account == '' || pin == '' || api_key == '' || api_secret == '') {
			e.preventDefault();
			account = jQuery('label[for=woocommerce_tigo_money_account]').text();
			jQuery('label[for=woocommerce_tigo_money_account]').html('<span style=\'color:red;\'>'+account+'</span>');
			pin = jQuery('label[for=woocommerce_tigo_money_pin]').text();
			jQuery('label[for=woocommerce_tigo_money_pin]').html('<span style=\'color:red;\'>'+pin+'</span>');
			api_key = jQuery('label[for=woocommerce_tigo_money_api_key]').text();
			jQuery('label[for=woocommerce_tigo_money_api_key]').html('<span style=\'color:red;\'>'+api_key+'</span>');
			api_secret = jQuery('label[for=woocommerce_tigo_money_api_secret]').text();
			jQuery('label[for=woocommerce_tigo_money_api_secret]').html('<span style=\'color:red;\'>'+api_secret+'</span>');
		}
	});
});");
		$this->form_fields = array(
            'url_callback'          => array(
                'title'       => __( 'Callback URL', 'tigo-money-woo'),
                'type'        => 'title',
                'description' => __( get_bloginfo( 'url' ), 'tigo-money-woo' ),
            ),
			'enabled' => array(
				'title' => __('Enable/Disable', 'tigo-money-woo'),
				'type' => 'checkbox',
				'label' => __('Enable Tigo Money', 'tigo-money-woo'),
				'default' => 'no'
			),
			'title' => array(
				'title' => __('Title', 'tigo-money-woo'),
				'type' => 'text',
				'description' => __('It corresponds to the title that the user sees during the checkout', 'tigo-money-woo'),
				'default' => __('Tigo Money', 'tigo-money-woo'),
				'desc_tip' => true,
			),

			'description' => array(
				'title' => __('Description', 'tigo-money-woo'),
				'type' => 'textarea',
				'description' => __('It corresponds to the description that the user will see during the checkout', 'tigo-money-woo'),
				'default' => __('Tigo Money simple payment only for Paraguay', 'tigo-money-woo'),
				'desc_tip' => true,
			),

			'account' => array(
				'title' => __('Agent account', 'tigo-money-woo'),
				'type'        => 'text',
				'description' => __('It is the electronic wallet used as how account
electronic money collector corresponding to Commerce', 'tigo-money-woo'),
				'desc_tip' => true,
				'default' => '',
			),

			'pin' => array(
				'title' => __('Agent PIN', 'tigo-money-woo'),
				'type'        => 'text',
				'description' => __('The agent pin', 'tigo-money-woo'),
				'desc_tip' => true,
				'default' => '',
			),

			'environment' => array(
				'title' => __('Environment', 'tigo-money-woo'),
				'type'        => 'select',
				'class'       => 'wc-enhanced-select',
				'description' => __('Enable to run tests', 'tigo-money-woo'),
				'desc_tip' => true,
				'default' => 'test',
				'options'     => array(
					'production'    => __( 'Production', 'tigo-money-woo' ),
					'test' => __( 'Test', 'tigo-money-woo' ),
				),
			),

			'api_key' => array(
				'title' => __('client_id', 'tigo-money-woo'),
				'type' => 'text',
				'description' => __('Unique identifier
client assigned during the registration process with Tigo
Money', 'epayco_woocommerce'),
				'default' => '',
				'desc_tip' => true,
				'placeholder' => ''
			),

			'api_secret' => array(
				'title' => __('client_secret
', 'tigo-money-woo'),
				'type' => 'password',
				'description' => __('Secret password provided during the registration process with Tigo Money', 'tigo-money-woo'),
				'default' => '',
				'desc_tip' => true,
				'placeholder' => ''
			),
		);
	}


	public function admin_options()
    {
        ?>
        <h3><?php _e('Tigo Money Woo', 'tigo-money-woo'); ?></h3>
        <p><?php echo $this->method_description; ?></p>
        <table class="form-table">
            <?php
            $this->test_tigo_money_woo_token();
            $this->generate_settings_html();
            ?>
        </table>
        <?php
    }


    public function validate_fields()
    {
        if (isset($_POST['error_tigo_money'])){
            wc_add_notice($_POST['error_tigo_money'], 'error' );
            return false;
        }

        return true;
    }

    public function process_payment($order_id)
    {

        $params = $_POST;
        $params['id_order'] = $order_id;

        $data = tigo_money_woo()->tigo_money_transaction($params);

        if($data['status']){
            wc_reduce_stock_levels($order_id);
            WC()->cart->empty_cart();
            return array(
                'result' => 'success',
                'redirect' => $data['url']
            );
        }else{
            wc_add_notice($data['message'], 'error' );
        }
    }


    public function payment_fields()
    {
        ?>
            <label for="number_subscriber_tigo_money">
                <?php echo __('Número Tigo Money ejemplo: (09xxxxxxxx)','tigo-money-woo');?>
            </label>
            <input type='tel' id="number_tigo_money" name="number_subscriber_tigo_money" required>
        <?php
    }

	public function order_received_message( $text, $order ) {
		if(!empty($_GET['msg'])){
			return $text .' '.$_GET['msg'];
		}
		return $text;
	}

	public function createUrl($token = false, $check = false)
	{
		if ($this->get_option('environment') == 'production'){
			$url = "https://prod.api.tigo.com/";
		}else{
			$url = "https://securesandbox.tigo.com/";
		}

		if ($token && $check == false){
			$url .= 'v1/oauth/mfs/payments/tokens';
		}elseif($token && $check){
			$url .= 'v2/tigo/mfs/payments/transactions/PRY/';
		}
		else{
			$url .= 'v2/tigo/mfs/payments/authorizations';
		}
		return $url;
	}

	public function test_tigo_money_woo_token()
	{
	    $access = $this->get_option('api_key') . ":" . $this->get_option('api_secret');
	    $access = base64_encode($access);
		$token = wp_safe_remote_post( $this->createUrl(true), array('headers' => array( 'cache-control' => 'no-cache','content-type'  => 'application/x-www-form-urlencoded', 'authorization' => 'Basic '. $access ),'body' => array( 'grant_type' => 'client_credentials')));
		$error = false;

		if ( is_wp_error( $token ) )
		    $error = true;

		if ( $token['response']['code'] != 200 )
		    $error = true;

		if($error)
			do_action('notices_action_tag_tigo_woo', __('Failed to connect, check client_id and client_secret accesses','tigo-money-woo'));
	}

}
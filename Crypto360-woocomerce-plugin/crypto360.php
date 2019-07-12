<?php
    /*
    * Plugin Name: WooCommerce Crypto360 Payment Gateway
    * Plugin URI: https://rudrastyh.com/woocommerce/payment-gateway-plugin.html
    * Description: Take cryptocurrency payments on your store.
    * Author: Block360
    * Author URI: http://block360.io
    * Version: 1.0.1
    */
    
    add_filter( 'woocommerce_payment_gateways', 'Crypto360_add_gateway_class' );
    function Crypto360_add_gateway_class( $gateways ) 
    {
        $gateways[] = 'WC_Crypto360_Gateway'; // your class name is here
        return $gateways;
    }
    
    function crypto360_action_links($links, $file)
    {
        static $thisPlugin;

        if (false === isset($thisPlugin) || true === empty($thisPlugin)) {
            $thisPlugin = plugin_basename(__FILE__);
        }

        if ($file == $thisPlugin) {
            $settingsLink = '<a href="'.get_bloginfo('wpurl')
                .'/wp-admin/admin.php?page=wc-settings&tab=checkout&section=crypto">Settings</a>';
            array_unshift($links, $settingsLink);
        }

        return $links;
    }

    add_filter('plugin_action_links', 'crypto360_action_links', 10, 2);

    /*
    * The class itself, please note that it is inside plugins_loaded action hook
    */
    add_action( 'plugins_loaded', 'Crypto360_init_gateway_class' );
    function Crypto360_init_gateway_class() {
    

        if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
            // oops!
            return;
        }
        class WC_Crypto360_Gateway extends WC_Payment_Gateway {
    
            /**
             * Class constructor
            */
            public function __construct() {
    
                $this->id = 'crypto'; // payment gateway plugin ID
                $this->icon = ''; // URL of the icon that will be displayed on checkout page near your gateway name
                $this->has_fields = false; // in case you need a custom credit card form
                $this->method_title = 'Crypto360 Payment Gateway';
                $this->method_description = 'Crypto360 allows you to receive cryptocurrency payments on your woocommerce store'; // will be displayed on the options page
                // Method with all the options fields
                $this->init_form_fields();
            
                // Load the settings.
                $this->init_settings();
                $this->title = $this->get_option( 'title' );
                $this->description = $this->get_option( 'description' );
                $this->enabled = $this->get_option( 'enabled' );
                $this->merchant_id 		= $this->get_option( 'merchant_id' );
                $this->api_key 		= $this->get_option( 'api_key' );
                $this->send_shipping	= $this->get_option( 'send_shipping' );
                
                // This action hook saves the settings
                add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
                // Payment listener/API hook
		        add_action( 'woocommerce_api_wc_crypto360_gateway', array( $this, 'check_ipn_response' ) );
            }
    
            /**
             * Plugin options, we deal with it in Step 3 too
            */
            public function init_form_fields(){
    
            
                $this->form_fields = array(
                    'enabled' => array(
                        'title'       => 'Enable/Disable',
                        'label'       => 'Enable Crypto360 Gateway',
                        'type'        => 'checkbox',
                        'description' => '',
                        'default'     => 'yes'
                    ),
                    'title' => array(
                        'title'       => 'Title',
                        'type'        => 'text',
                        'description' => 'This controls the title which the user sees during checkout.',
                        'default'     => 'Crypto360',
                        'desc_tip'    => true
                    ),
                    'description' => array(
                        'title'       => 'Description',
                        'type'        => 'textarea',
                        'description' => 'This controls the description which the user sees during checkout.',
                        'default'     => 'Pay with Bitcoin via Crypto360.'
                    ),
                    'merchant_id' => array(
                                    'title' => 'Merchant ID',
                                    'type' 			=> 'text',
                                    'description' => 'Please enter your Crypto360 Merchant ID.',
                                    'default' => ''
                    ),
                    'api_key' => array(
                        'title' => 'API Key',
                        'type' 			=> 'text',
                        'description' => 'Please enter your Crypto360 API Key.',
                        'default' => ''
        ),
                    'send_shipping' => array(
                                    'title' => 'Collect Shipping Info?', 'woocommerce',
                                    'type' => 'checkbox',
                                    'label' => 'Enable Shipping Information on Checkout page',
                                    'default' => 'yes'
                                )
                );
            }

            public function process_payment( $order_id ) {
 
                global $woocommerce;
             
                // we need it to get any order detailes
                $order = wc_get_order( $order_id );
             
                /*
                  * Array with parameters for API interaction
                 */
                
             
                /*
                 * Your API interaction could be built with wp_remote_post()
                  */

                        $order->reduce_order_stock();
                        $woocommerce->cart->empty_cart();
                        // Redirect to the thank you page
                        return array(
                            'result' => 'success',
                            'redirect' => $this->generate_crypto360_url($order) //$this->get_return_url( $order )
                        );
             
            }

            function generate_crypto360_url($order) {
                global $woocommerce;
                
                $coinpayments_adr = "http://localhost/crypto360/order.php?";
                $coinpayments_args = $this->get_coinpayments_args( $order );
                $coinpayments_adr .= http_build_query( $coinpayments_args, '', '&' );
                return $coinpayments_adr;
            }

            function get_coinpayments_args( $order) {
                global $woocommerce;
        
                $order_id = $order->id;
        
                
        
                // CoinPayments.net Args
                $coinpayments_args = array(
                        'merchant' 				=> $this->merchant_id,
                        'api_key' 				=> $this->api_key,
                        'currency' 		=> $order->get_currency(),
                        'success_url' 				=> add_query_arg( 'wc-api', 'WC_Crypto360_Gateway', home_url( '/' ) ),//get_return_url( $order ),
                        'cancel_url'			=> esc_url_raw($order->get_cancel_order_url_raw()),
        
                        // Order key + ID
                        'invoice'				=> $this->invoice_prefix . $order->get_order_number(),
                        'email'					=> $order->billing_email,
                );
        
                
        
                if ($this->simple_total) {
                    $coinpayments_args['amountf'] 		= number_format( $order->get_total(), 8, '.', '' );
                    $coinpayments_args['taxf'] 				= 0.00;
                    $coinpayments_args['shippingf']		= 0.00;
                } else {
                    $coinpayments_args['amountf'] 		= number_format( $order->get_total() - $order->get_total_shipping() - $order->get_total_tax(), 8, '.', '' );
                    $coinpayments_args['shippingf']		= number_format( $order->get_total_shipping(), 8, '.', '' );
                    $coinpayments_args['taxf']				= $order->get_total_tax();
                }
        
                return $coinpayments_args;
            }
    
            function check_ipn_response()
            {
                $order = wc_get_order( $_GET['order_id'] );
                $order->payment_complete();
            }
        }
    }
?>

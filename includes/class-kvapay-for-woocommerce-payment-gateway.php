<?php
/**
 * The functionality of the kvapay payment gateway.
 *
 * @link       https://kvapay.com
 * @since      1.0.0
 *
 * @package    Kvapay_For_Woocommerce
 * @subpackage Kvapay_For_Woocommerce/includes
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

if (!class_exists('WC_Payment_Gateway')) {
    return;
}

use Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry;
use KvaPay\Exception\ApiErrorException;
use KvaPay\Client;

/**
 * The functionality of the kvapay payment gateway.
 *
 * @since      1.0.0
 * @package    Kvapay_For_Woocommerce
 * @subpackage Kvapay_For_Woocommerce/includes
 * @author     KvaPay <support@kvapay.com>
 */
class Kvapay_For_Woocommerce_Payment_Gateway extends WC_Payment_Gateway
{

    public const ORDER_TOKEN_META_KEY = 'kvapay_order_token';

    public const SETTINGS_KEY = 'woocommerce_kvapay_settings';

    /**
     * Kvapay_Payment_Gateway constructor.
     */
    public function __construct()
    {
        $this->id = 'kvapay';
        $this->has_fields = true;
        $this->method_title = 'KvaPay';
        $this->new_method_label = __('Pay with Cryptocurrency', 'kvapay');
        $this->icon = apply_filters('woocommerce_kvapay_icon', KVAPAY_FOR_WOOCOMMERCE_PLUGIN_URL . 'assets/kvapay.png');
        $this->method_description = __('Accept Bitcoin and Altcoins via KvaPay in your WooCommerce store.', 'kvapay');

        $this->init_form_fields();
        $this->init_settings();

        $this->title = $this->get_option('title');
        $this->description = $this->get_option('description');
        $this->api_key = $this->get_option('api_key');
        $this->api_secret = $this->get_option('api_secret');
        $this->order_statuses = $this->get_option('order_statuses');
        $this->test = ('yes' === $this->get_option('test', 'no'));

        add_action('woocommerce_update_options_payment_gateways_kvapay', array($this, 'process_admin_options'));
        add_action('woocommerce_update_options_payment_gateways_kvapay', array($this, 'save_order_statuses'));
        add_action('woocommerce_thankyou_kvapay', array($this, 'thankyou'));
        add_action('woocommerce_api_wc_gateway_kvapay', array($this, 'payment_callback'));
    }

    /**
     * Output the gateway settings screen.
     */
    public function admin_options()
    {
        ?>
        <h3>
            <?php
            esc_html_e('KvaPay', 'kvapay');
            ?>
        </h3>
        <p>
            <?php
            esc_html_e('Accept Bitcoin through the KvaPay.com', 'kvapay');
            ?>
            <br>
            <a href="mailto:support@kvapay.com">support@kvapay.com</a>
        </p>

        <p>1) Account Creation: To get started, visit <a href="<?php echo esc_url('https://kvapay.com/signup'); ?>" target="_blank">https://kvapay.com/signup</a> and complete the registration process.</p>
        <p>2) Configuration: Enter your <b>API KEY</b> and <b>API SECRET</b> from your Kvapay account into the appropriate fields. Adjust any additional settings as needed.</p>
        <p>3) Adding Callback URL: Specify your callback URL to ensure proper integration and transaction processing.<b>
                <?php
                echo esc_url(trailingslashit(get_bloginfo('wpurl')) . '?wc-api=wc_gateway_kvapay');
                ?>
            </b>
        </p>



        <table class="form-table">
            <?php
            $this->generate_settings_html();
            ?>
        </table>
        <?php
    }

    /**
     * Initialise settings form fields.
     */
    public function init_form_fields()
    {
        $this->form_fields = array(
            'enabled' => array(
                'title' => __('Enable KvaPay', 'kvapay'),
                'label' => __('Enable Cryptocurrency payments via KvaPay', 'kvapay'),
                'type' => 'checkbox',
                'description' => '',
                'default' => 'no',
            ),
            'description' => array(
                'title' => __('Description', 'kvapay'),
                'type' => 'textarea',
                'description' => __('The payment method description which a user sees at the checkout of your store.', 'kvapay'),
                'default' => __('Pay with BTC, LTC, ETH, USDT and other cryptocurrencies. Powered by KvaPay.', 'kvapay'),
            ),
            'title' => array(
                'title' => __('Title', 'kvapay'),
                'type' => 'text',
                'description' => __('The payment method title which a customer sees at the checkout of your store.', 'kvapay'),
                'default' => __('Cryptocurrencies via KvaPay', 'kvapay'),
            ),
            'api_key' => array(
                'title' => __('API Key', 'kvapay'),
                'type' => 'text',
                'description' => __('KvaPay API Key', 'kvapay'),
                'default' => '',
            ),
            'api_secret' => array(
                'title' => __('API Secret', 'kvapay'),
                'type' => 'text',
                'description' => __('KvaPay API Secret', 'kvapay'),
                'default' => '',
            ),
            'order_statuses' => array(
                'type' => 'order_statuses',
            ),
            'test' => array(
                'title' => __('Test', 'kvapay'),
                'type' => 'checkbox',
                'label' => __('Test Mode', 'kvapay'),
                'default' => 'no',
                'description' => __(
                        "To test on KvaPay Test, turn Test Mode 'On'. Please note, for Test Mode you must create a separate account on dev.crypay.com and generate API credentials there. API credentials generated on kvapay.com are 'Live' credentials and will not work for 'Test' mode",
                    'kvapay'
                ),
            ),
        );
    }

    /**
     * Thank you page.
     */
    public function thankyou()
    {
        $description = $this->get_description();
        if ($description) {
            echo '<p>' . esc_html($description) . '</p>';
        }
    }

    /**
     * Payment process.
     *
     * @param int $order_id The order ID.
     * @return string[]
     *
     * @throws Exception Unknown exception type.
     */
    public function process_payment($order_id)
    {
        global $woocommerce, $page, $paged;
        $order = wc_get_order($order_id);

        $order->update_status('pending');

        wc_reduce_stock_levels($order_id);
        WC()->cart->empty_cart();

        $client = $this->init_kvapay();


        $params = [
            'variableSymbol' => (string)$order_id,
            'amount' => (float)$order->get_total(),
            'symbol' => $order->get_currency(),
            'currency' => $order->get_currency(),
            'failUrl' => $this->get_fail_order_url($order),
            'successUrl' => add_query_arg('order-received', $order->get_id(), add_query_arg('key', $order->get_order_key(), $this->get_return_url($order))),
            'timestamp' => time(),
            'email' => $order->get_billing_email(),
            'name' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
        ];

        $response = array('result' => 'fail');

        try {
            $gateway_order = $client->payment->createPaymentShortLink($params);
            if ($gateway_order) {
                $response['result'] = 'success';
                $response['redirect'] = $gateway_order->shortLink;
            }
        } catch (ApiErrorException $exception) {
            error_log($exception->getMessage());
        }

        return $response;
    }

    /**
     * Payment callback.
     *
     * @throws Exception Unknown exception type.
     */
    public function payment_callback()
    {
        $request = file_get_contents('php://input');

        $client = $this->init_kvapay();

        $signature = null;

        if ( isset( $_SERVER['HTTP_X_SIGNATURE'] ) ) {
            $signature = sanitize_text_field( $_SERVER['HTTP_X_SIGNATURE'] );
        }

        if ($signature != $client->generateSignature($request, $this->settings['api_secret'])) {
            throw new Exception('KvaPay callback signature does not valid');
        }

        $request = json_decode($request, true);

        $order = wc_get_order(sanitize_text_field($request['variableSymbol']));

        if (!$order || !$order->get_id()) {
            throw new Exception('Order #' . $order->get_id() . ' does not exists');
        }

        if ($order->get_payment_method() !== $this->id) {
            throw new Exception('Order #' . $order->get_id() . ' payment method is not ' . $this->method_title);
        }

        $callback_order_status = sanitize_text_field($request['state']);

        $order_statuses = $this->get_option('order_statuses');

        $wc_order_status = isset($order_statuses[$callback_order_status]) ? $order_statuses[$callback_order_status] : null;
        if (!$wc_order_status) {
            return;
        }

        switch ($callback_order_status) {
            case 'SUCCESS':
                if (!$this->is_order_paid_status_valid($order, $request['amount'])) {
                    throw new Exception('KvaPay Order #' . $order->get_id() . ' amounts do not match');
                }

                $status_was = 'wc-' . $order->get_status();

                $this->handle_order_status($order, $wc_order_status);
                $order->add_order_note(__('Payment is confirmed on the network, and has been credited to the merchant. Purchased goods/services can be securely delivered to the buyer.', 'kvapay'));
                $order->payment_complete();

                $wc_expired_status = $order_statuses['EXPIRED'];

                if ('processing' === $order->status && ($status_was === $wc_expired_status)) {
                    WC()->mailer()->emails['WC_Email_Customer_Processing_Order']->trigger($order->get_id());
                }
                if (('processing' === $order->status || 'completed' === $order->status) && ($status_was === $wc_expired_status)) {
                    WC()->mailer()->emails['WC_Email_New_Order']->trigger($order->get_id());
                }
                break;
            case 'WAITING_FOR_CONFIRMATION':
                $this->handle_order_status($order, $wc_order_status);
                $order->add_order_note(__('Shopper transferred the payment for the invoice. Awaiting blockchain network confirmation.', 'kvapay'));
                break;
            case 'EXPIRED':
                $this->handle_order_status($order, $wc_order_status);
                $order->add_order_note(__('Buyer did not pay within the required time and the invoice expired.', 'kvapay'));
                break;
        }
    }

    /**
     * Generates a URL so that a customer can cancel their (unpaid - pending) order.
     *
     * @param WC_Order $order Order.
     * @param string $redirect Redirect URL.
     * @return string
     */
    public function get_fail_order_url($order, $redirect = '')
    {
        return apply_filters(
            'woocommerce_get_cancel_order_url',
            wp_nonce_url(
                add_query_arg(
                    array(
                        'order' => $order->get_order_key(),
                        'order_id' => $order->get_id(),
                        'redirect' => $redirect,
                    ),
                    $order->get_cancel_endpoint()
                ),
                'woocommerce-cancel_order'
            )
        );
    }

    /**
     * Generate order statuses.
     *
     * @return false|string
     */
    public function generate_order_statuses_html()
    {
        ob_start();

        $cg_statuses = $this->kvapay_order_statuses();
        $default_status['ignore'] = __('Do nothing', 'kvapay');
        $wc_statuses = array_merge($default_status, wc_get_order_statuses());

        $default_statuses = array(
            'SUCCESS' => 'wc-processing',
            'WAITING_FOR_CONFIRMATION' => 'ignore',
            'EXPIRED' => 'ignore',
        );

        ?>
        <tr valign="top">
            <th scope="row" class="titledesc"> <?php esc_html_e('Order Statuses:', 'kvapay'); ?></th>
            <td class="forminp" id="kvapay_order_statuses">
                <table cellspacing="0">
                    <?php
                    foreach ($cg_statuses as $cg_status_name => $cg_status_title) {
                        ?>
                        <tr>
                            <th><?php echo esc_html($cg_status_title); ?></th>
                            <td>
                                <select name="woocommerce_kvapay_order_statuses[<?php echo esc_html($cg_status_name); ?>]">
                                    <?php
                                    $cg_settings = get_option(static::SETTINGS_KEY);
                                    $order_statuses = $cg_settings['order_statuses'];

                                    foreach ($wc_statuses as $wc_status_name => $wc_status_title) {
                                        $current_status = isset($order_statuses[$cg_status_name]) ? $order_statuses[$cg_status_name] : null;

                                        if (empty($current_status)) {
                                            $current_status = $default_statuses[$cg_status_name];
                                        }

                                        if ($current_status === $wc_status_name) {
                                            echo '<option value="' . esc_attr($wc_status_name) . '" selected>' . esc_html($wc_status_title) . '</option>';
                                        } else {
                                            echo '<option value="' . esc_attr($wc_status_name) . '">' . esc_html($wc_status_title) . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                </table>
            </td>
        </tr>
        <?php

        return ob_get_clean();
    }

    /**
     * Validate order statuses field.
     *
     * @return mixed|string
     */
    public function validate_order_statuses_field()
    {
        $order_statuses = $this->get_option('order_statuses');

        if (isset($_POST[$this->plugin_id . $this->id . '_order_statuses'])) {
            return array_map(
                'sanitize_text_field',
                wp_unslash($_POST[$this->plugin_id . $this->id . '_order_statuses'])
            );
        }

        return $order_statuses;
    }

    /**
     * Save order statuses.
     */
    public function save_order_statuses()
    {
        $kvapay_order_statuses = $this->kvapay_order_statuses();
        $wc_statuses = wc_get_order_statuses();

        if (isset($_POST['woocommerce_kvapay_order_statuses'])) {
            $cg_settings = get_option(static::SETTINGS_KEY);
            $order_statuses = $cg_settings['order_statuses'];

            foreach ($kvapay_order_statuses as $cg_status_name => $cg_status_title) {
                if (!isset($_POST['woocommerce_kvapay_order_statuses'][$cg_status_name])) {
                    continue;
                }

                $wc_status_name = sanitize_text_field(wp_unslash($_POST['woocommerce_kvapay_order_statuses'][$cg_status_name]));

                if (array_key_exists($wc_status_name, $wc_statuses)) {
                    $order_statuses[$cg_status_name] = $wc_status_name;
                }
            }

            $cg_settings['order_statuses'] = $order_statuses;
            update_option(static::SETTINGS_KEY, $cg_settings);
        }
    }

    /**
     * Handle order status.
     *
     * @param WC_Order $order The order.
     * @param string $status Order status.
     */
    protected function handle_order_status(WC_Order $order, string $status)
    {
        if ('ignore' !== $status) {
            $order->update_status($status);
        }
    }

    /**
     * List of kvapay order statuses.
     *
     * @return string[]
     */
    private function kvapay_order_statuses()
    {
        return [
            'SUCCESS' => 'SUCCESS',
            'WAITING_FOR_CONFIRMATION' => 'WAITING_FOR_CONFIRMATION',
            'EXPIRED' => 'EXPIRED',
        ];
    }

    /**
     * Initial client.
     *
     * @return Client
     */
    private function init_kvapay()
    {

        $client = new Client($this->api_key, $this->test);
        $client::setAppInfo('Kvapay For Woocommerce', KVAPAY_FOR_WOOCOMMERCE_VERSION);

        return $client;
    }

    /**
     * Check if order status is valid.
     *
     * @param WC_Order $order The order.
     * @param mixed $price Price.
     * @return bool
     */
    private function is_order_paid_status_valid(WC_Order $order, $price)
    {
        return $order->get_total() >= (float)$price;
    }

    /**
     * Check token match.
     *
     * @param WC_Order $order The order.
     * @param string $token Token.
     * @return bool
     */
    private function is_token_valid(WC_Order $order, string $token)
    {
        $order_token = $order->get_meta(static::ORDER_TOKEN_META_KEY);

        return !empty($order_token) && $token === $order_token;
    }

}

<?php
namespace Pay2\Pay2;

use Dotenv\Dotenv;

/**
 * Class Client
 * @package Pay2\Pay2
 */
class Client
{
    /**
     * API client version
     *
     * @var string
     */
    public static $clientVersion = '0.2.0.1';

    /**
     * Start payment url
     *
     * @var string
     */
    public static $paymentStartUrl = 'https://pay2.hu/Gateway/RequestPayment.php?v3';

    /**
     * API url
     *
     * @var string
     */
    public static $apiUrl = 'https://pay2.hu/api/';

    /**
     * Client API
     *
     * @var string
     */
    public static $clientApiUrl = 'https://api.pay2.hu/';

    /**
     * Client JS
     *
     * @var string
     */
    public static $clientApiJs = 'clients_v1.js#bc=';

    /**
     * Assets url
     *
     * @var string
     */
    public static $paymentAssetsUrl = 'https://pay2.hu/paymentAssets/';

    /**
     * API endpoints
     *
     * @var string[]
     */
    public static $apiEndpoints = [
        'client' => 'client',
        'transaction_status' => 'transaction/status'
    ];

    /**
     * Assets endpoints
     *
     * @var string[]
     */
    public static $assetsEndpoints = [
        'otp' => 'vendor/otpsimple',
        'barion' => 'vendor/barion',
    ];

    /**
     * @var Dotenv
     */
    private $dotenv;

    /**
     * Client constructor.
     * With $useLegacyDotenv you can use vlucas/phpdotenv 3.x in older projects/frameworks versions
     *
     * @param false $useLegacyDotenv
     */
    public function __construct($useLegacyDotenv = false)
    {
        switch ($useLegacyDotenv) {
            case true:
                $this->dotenv = Dotenv::create(__DIR__, '/../../../../../.env');
                break;
            default:
                $this->dotenv = Dotenv::createMutable($_SERVER['DOCUMENT_ROOT']);
        }

        $this->dotenv->load();
    }

    /**
     * Get client api js
     *
     * @return string
     */
    public function getPaymentApiJs()
    {
        return '<script type="text/javascript" src="'.self::$clientApiUrl.self::$clientApiJs.self::$clientVersion.'"></script>';
    }

    /**
     * Add metrics to checkout
     *
     * @param $orderStatus
     * @param $orderId
     * @return string
     */
    public function checkoutMetricsJs($orderStatus, $orderId)
    {
        return "<script>
            if (typeof client.pushData !== 'undefined') {
                let shopOrder = {
                    meta_data: {
                        orderStatus: '$orderStatus',
                        orderRef: $orderId
                    }
                };

                client.pushData(shopOrder);
            }
        </script>";
    }

    /**
     * Get mandatory vendor assets from Pay2
     *
     * @return mixed
     */
    public function getPaymentAssets()
    {
        $paymentVendor = $_ENV['PAY2_VENDOR'];

        switch ($paymentVendor) {
            case 'otpsimple':
                $paymentAssets = self::$paymentAssetsUrl.self::$assetsEndpoints['otp'];
                break;
            case 'barion':
                $paymentAssets = self::$paymentAssetsUrl.self::$assetsEndpoints['barion'];
                break;
            default:
                $paymentAssets = self::$paymentAssetsUrl.self::$assetsEndpoints['otp'];
        }

        return json_decode(file_get_contents($paymentAssets));
    }

    /**
     * Generate Pay2 site secret key for authorization
     *
     * @param $sitePin
     * @param $sitePublic
     * @return false|string
     */
    public function generateSecretKey($sitePin, $sitePublic)
    {
        if (empty($sitePin)){
            return false;
        }

        return hash('sha256', $sitePublic.$sitePin);
    }

    /**
     * Generate Pay2 payment form
     *
     * @param $orderId
     * @param $payTotal
     * @param $customerData
     * @param $paymentTosHtml
     * @param false $productData
     * @param false $customCallback
     * @return string
     */
    public function buildForm($orderId, $payTotal, $customerData, $paymentTosHtml, $productData = false, $customCallback = false)
    {
        $transactionData = array(
            'site_pin' => $_ENV['PAY2_SITE_PIN'],
            'site_secret' => $this->generateSecretKey($_ENV['PAY2_SITE_PIN'], $_ENV['PAY2_SITE_PUBLIC']),
            'client_callback' => ($customCallback) ? $customCallback : $_ENV['PAY2_CALLBACK_URL'],
            'order_id' => $orderId,
            'pay_total' => $payTotal,
            'name' => $customerData['name'],
            'phone' => $customerData['phone'],
            'email' => $customerData['email'],
            'state' => $customerData['state'],
            'zip' => $customerData['zip'],
            'city' => $customerData['city'],
            'address' => $customerData['address'],
            'product' => [
                'name' => ($productData) ? $productData['name'] : '',
                'desc' => ($productData) ? $productData['desc'] : '',
                'qty' => ($productData) ? $productData['qty'] : '',
                'sku' => ($productData) ? $productData['sku'] : '',
            ]
        );

        switch($paymentTosHtml) {
            case true:
                $form = '<form action="'.self::$paymentStartUrl.'" method="post">
                <input type="hidden" name="clientVersion" value="'.self::$clientVersion.'" />
                <input type="hidden" name="transactionData" value="'.base64_encode(json_encode($transactionData)).'" />

                <label class="pay2payment-form__label pay2payment-form__label-for-checkbox checkbox">
                    <input type="checkbox" class="pay2payment-form__input pay2payment-form__input-checkbox input-checkbox" name="terms-payment" id="terms-payment" required>
                    <span class="pay2payment-terms-and-conditions-checkbox-text">
                        '.$paymentTosHtml.'
                    </span>
                    <span class="required">*</span>
                </label>
                <div class="text-center">
                    <button class="btn btn-primary btn-lg" type="submit" style="font-size: 24px; margin-top: 10px">
                        <i class="fa fa-credit-card" aria-hidden="true"></i>
                        '.$_ENV['PAY2_SUBMIT_BUTTON'].'
                    </button>
                </div>
            </form>';
                break;

            default:
                $form = '<form action="'.self::$apiUrl.'" method="post">
                <input type="hidden" name="clientVersion" value="'.self::$clientVersion.'" />
                <input type="hidden" name="transactionData" value="'.base64_encode(json_encode($transactionData)).'" />

                <div class="text-center">
                    <button class="btn btn-primary btn-lg" type="submit" style="font-size: 24px; margin-top: 10px">
                        <i class="fa fa-credit-card" aria-hidden="true"></i>
                        '.$_ENV['PAY2_SUBMIT_BUTTON'].'
                    </button>
                </div>
            </form>';
        }

        return $form;
    }

    /**
     * Test callback response from Pay2
     * DO NOT USE PRODUCTION MODE!
     *
     * @param $callbackRequest
     */
    public function testCallback($callbackRequest)
    {
        echo "<pre>";
        var_dump($callbackRequest);
        echo "</pre>";

        if(isset($callbackRequest['message'])) {
            echo "<pre>";
            print_r(base64_decode($callbackRequest['message']));
            echo "</pre>";
        }
    }

    /**
     * Get transaction status with original orderId
     *
     * @param $orderId
     * @return mixed
     */
    public function getPaymentStatus($orderId)
    {
        $status = self::$apiUrl.self::$apiEndpoints['transaction_status'].'/'.$orderId;

        return json_decode(file_get_contents($status));
    }
}

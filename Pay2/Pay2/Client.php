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
    public static $clientVersion = '0.4.0';

    /**
     * Start payment url
     *
     * @var string
     */
    public static $paymentStartUrl = 'https://v2.pay2.hu/Gateway/RequestPayment.php?v3';
    public static $paymentStartUrlDEV = 'https://sandbox.v2.pay2.hu/Gateway/RequestPayment.php?v3';

    /**
     * API url
     *
     * @var string
     */
    public static $apiUrl = 'https://v2.pay2.hu/api/';
    public static $apiUrlDEV = 'https://sandbox.v2.pay2.hu/api/';

    /**
     * Client API
     *
     * @var string
     */
    public static $clientApiUrl = 'https://v2.pay2.hu/';

    /**
     * Client JS
     *
     * @var string
     */
    public static $clientApiJs = 'clients_v1.js#v=';

    /**
     * Assets url
     *
     * @var string
     */
    public static $paymentAssetsUrl = 'https://v2.pay2.hu/paymentAssets/';

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
            if (typeof pay2client.pushData !== 'undefined') {
                let shopOrder = {
                    meta_data: {
                        orderStatus: '$orderStatus',
                        orderRef: '$orderId'
                    }
                };

                pay2client.pushData(shopOrder);
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
     * Get mandatory vendor js scripts
     *
     * @param $trackingId
     * @return string|null
     */
    public function getPaymentVendorJs($trackingId)
    {
        $paymentVendor = $_ENV['PAY2_VENDOR'];

        if($paymentVendor == 'barion') {
            return '<script>
                // Create BP element on the window
                window["bp"] = window["bp"] || function () {
                    (window["bp"].q = window["bp"].q || []).push(arguments);
                };
                window["bp"].l = 1 * new Date();

                // Insert a script tag on the top of the head to load bp.js
                scriptElement = document.createElement("script");
                firstScript = document.getElementsByTagName("script")[0];
                scriptElement.async = true;
                scriptElement.src = \'https://pixel.barion.com/bp.js\';
                firstScript.parentNode.insertBefore(scriptElement, firstScript);
                window[\'barion_pixel_id\'] = \''.$trackingId.'\';

                // Send init event
                bp(\'init\', \'addBarionPixelId\', window[\'barion_pixel_id\']);
            </script>

            <noscript>
                <img height="1" width="1" style="display:none" alt="Barion Pixel" src="https://pixel.barion.com/a.gif?ba_pixel_id='.$trackingId.'&ev=contentView&noscript=1">
            </noscript>';
        }

        return null;
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
     * @param false $testMode
     * @return string
     */
    public function buildForm($orderId, $payTotal, $customerData, $paymentTosHtml, $productData = false, $customCallback = false, $testMode = false)
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

        switch ($testMode) {
            case true:
                $paymentUrl = self::$paymentStartUrlDEV;
                break;
            default:
                $paymentUrl = self::$paymentStartUrl;
        }

        switch($paymentTosHtml) {
            case true:
                $form = '<form action="'.$paymentUrl.'" method="post">
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
                $form = '<form action="'.$paymentUrl.'" method="post">
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
     * @param false $testMode
     * @return mixed
     */
    public function getPaymentStatus($orderId, $testMode = false)
    {
        switch ($testMode) {
            case true:
                $apiUrl = self::$apiUrlDEV;
                break;
            default:
                $apiUrl = self::$apiUrl;
        }

        $status = $apiUrl.self::$apiEndpoints['transaction_status'].'/'.$orderId;

        return json_decode(file_get_contents($status));
    }
}

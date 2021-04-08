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
     * @var string
     */
    public static $clientVersion = '0.1.7.1';
    
    /**
     * @var string
     */
    public static $apiUrl = 'https://pay2.hu/Gateway/RequestPayment.php?v3';

    /**
     * @var string
     */
    public static $otpSimpleAssets = 'https://pay2.hu/paymentAssets/vendor/otpsimple';

    /**
     * @var string
     */
    public static $barionAssets = 'https://pay2.hu/paymentAssets/vendor/barion';

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
     * Get mandatory vendor assets from Pay2
     *
     * @return mixed
     */
    public function getPaymentAssets()
    {
        $paymentVendor = $_ENV['PAY2_VENDOR'];

        switch ($paymentVendor) {
            case 'otpsimple':
                $paymentAssets = self::$otpSimpleAssets;
                break;
            case 'barion':
                $paymentAssets = self::$barionAssets;
                break;
            default:
                $paymentAssets = self::$otpSimpleAssets;
        }

        return json_decode(file_get_contents($paymentAssets));
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

        if($callbackRequest['message']) {
            echo "<pre>";
            print_r(base64_decode($callbackRequest['message']));
            echo "</pre>";
        }
    }

    /**
     * Generate Pay2 payment form
     *
     * @param $orderId
     * @param $payTotal
     * @param $customerData
     * @param $paymentTosHtml
     * @param bool $customCallback
     * @return string
     */
    public function buildForm($orderId, $payTotal, $customerData, $paymentTosHtml, $customCallback = false)
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
        );
        
        return '<form action="'.self::$apiUrl.'" method="post">
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
    }
}

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

    public function __construct()
    {
        $this->dotenv = Dotenv::createMutable(__DIR__.'/../../');
        $this->dotenv->load();
    }

    /**
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
     * @param $orderId
     * @param $payTotal
     * @param $customerData
     * @param $paymentTosHtml
     * @return string
     */
    public function buildForm($orderId, $payTotal, $customerData, $paymentTosHtml)
    {
        $transactionData = array(
            'site_pin' => $_ENV['PAY2_SITE_PIN'],
            'site_secret' => $this->generateSecretKey($_ENV['PAY2_SITE_PIN'], $_ENV['PAY2_SITE_PUBLIC']),
            'client_callback' => $_ENV['PAY2_CALLBACK_URL'],
            'order_id' => $orderId,
            'pay_total' => $payTotal,
            'name' => $customerData['name'],
            'email' => $customerData['email'],
            'city' => $customerData['city'],
            'zip' => $customerData['zip'],
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
                        Biztonságos bankkártyás fizetés
                    </button>
                </div>
            </form>';
    }
}

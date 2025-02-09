<?php
/* 
    1. get_credentials()
    2. create_order($amount,$receipt='')
    3. fetch_payments($id ='')
    4. capture_payment($amount, $id, $currency = "INR")
    5. verify_payment($order_id, $razorpay_payment_id, $razorpay_signature)

    0. curl($url, $method = 'GET', $data = [])
*/
class Phonepe
{
    private $salt_index = "";
    private $salt_key = "";
    private $merchant_id = "";
    private $url = "";
    private $environment = "";
    private $app_id = "";

    function __construct()
    {
        $settings = get_settings('payment_method', true);

        $system_settings = get_settings('system_settings', true);

        $this->salt_index = (isset($settings['phonepe_salt_index'])) ? $settings['phonepe_salt_index'] : " ";
        $this->app_id = (isset($settings['phonepe_appid'])) ? $settings['phonepe_appid'] : " ";
        $this->salt_key = (isset($settings['phonepe_salt_key'])) ? $settings['phonepe_salt_key'] : " ";
        $this->merchant_id = (isset($settings['phonepe_marchant_id'])) ? $settings['phonepe_marchant_id'] : " ";
        $this->url = (isset($settings['phonepe_payment_mode']) && $settings['phonepe_payment_mode'] == "live") ? "https://api.phonepe.com/apis/hermes" : "https://api-preprod.phonepe.com/apis/pg-sandbox";
        $this->environment = isset($settings['phonepe_payment_mode']) ? $settings['phonepe_payment_mode'] : 'UAT';
    }

    public function get_credentials()
    {
        $data['salt_index'] = $this->salt_index;
        $data['salt_key'] = $this->salt_key;
        $data['phonepe_appid'] = $this->app_id;
        $data['merchant_id'] = $this->merchant_id;
        $data['url'] = $this->url;
        return $data;
    }

    public function pay($data)
    {

        $data['merchantId'] = $this->merchant_id;
        $data['app_id'] = $this->app_id;  // ini_set('display_errors', 1);
        $data['callbackUrl'] = base_url("app/v1/api/phonepe_webhook"); // ini_set('display_errors', 1);
        $data['environment'] = $this->environment;
        $data['paymentInstrument'] = array(
            'type' => 'PAY_PAGE',
        );
        $url = $this->url . '/pg/v1/pay';
        $method = 'POST';

        /** generating a X-VERIFY header */
        $encode = base64_encode(json_encode($data));
        $saltKey = $this->salt_key;
        $saltIndex = $this->salt_index;
        $string = $encode . '/pg/v1/pay' . $saltKey;
        $sha256 = hash('sha256', $string);
        $finalXHeader = $sha256 . '###' . $saltIndex;

        $header = [
            "Content-Type: application/json",
            "accept: application/json",
            "X-VERIFY: $finalXHeader"
        ];
        $response = $this->curl($url, $method, json_encode(['request' => $encode]), $header);
        $res = json_decode($response['body'], true);
        return $res;
    }

    public function phonepe_payment($data)
    {
        $data['merchantId'] = $this->merchant_id;
        $url = $this->url . '/pg/v1/pay';
        $method = 'POST';

        /** generating a X-VERIFY header */
        $encode = base64_encode(json_encode($data));
        $saltKey = $this->salt_key;
        $saltIndex = $this->salt_index;
        $string = $encode . '/pg/v1/pay' . $saltKey;
        $sha256 = hash('sha256', $string);
        $finalXHeader = $sha256 . '###' . $saltIndex;

        $header = [
            "Content-Type: application/json",
            "accept: application/json",
            "X-VERIFY: $finalXHeader"
        ];
        $response = $this->curl($url, $method, json_encode(['request' => $encode]), $header);
        $res = json_decode($response['body'], true);
        return $res;
    }

    public function phonepe_checksum($data)
    {

        $phonePeAppId = $this->app_id;
        $phonePeMode = $this->environment;
        $phonePeMerId = $this->merchant_id;
        $phonePeEndPointUrl = base_url("app/v1/api/phonepe_webhook");
        $phonePeSaltKey = $this->salt_key;
        $phonePeSaltIndex = $this->salt_index;
        $totalPrice = $data['final_total'];
        $userMobileNumber = $data['mobile'];

        $amt = (int)round($totalPrice * 100);
        $jsonData = [
            "merchantId" => $phonePeMerId,
            "merchantUserId" => $phonePeMerId,
            "merchantTransactionId" => $data['order_id'],
            "amount" => $amt,
            "redirectMode" => "REDIRECT",
            "callbackUrl" => $phonePeEndPointUrl,
            "mobileNumber" => $userMobileNumber,
            "paymentInstrument" => ["type" => "PAY_PAGE"]
        ];
        $base64Data = base64_encode(json_encode($jsonData, JSON_UNESCAPED_SLASHES));
        $apiEndPoint = "/pg/v1/pay";
        $dataToHash = $base64Data . $apiEndPoint . $phonePeSaltKey;
        $sHA256 = hash('sha256', $dataToHash);
        $checksum = $sHA256 . '###' . $phonePeSaltIndex;
        $data =  [
            "payload" => $jsonData,
            "checksum" => $checksum,
            "appId" => $phonePeAppId,
            "environment" => $phonePeMode
        ];
        return $data;
    }

    public function check_status($id = '')
    {

        $data['merchantId'] = $this->merchant_id;
        $data['paymentInstrument'] = array(
            'type' => 'PAY_PAGE',
        );
        $endpoint = "/pg/v1/status/$this->merchant_id/$id";
        $url = $this->url . $endpoint;
        $method = 'GET';

        /** generating a X-VERIFY header */
        $saltKey = $this->salt_key;
        $saltIndex = $this->salt_index;
        $string = $endpoint . "" . $saltKey;
        $sha256 = hash('sha256', $string);
        $finalXHeader = $sha256 . '###' . $saltIndex;

        $header = [
            "Content-Type: application/json",
            "X-VERIFY: $finalXHeader",
            "X-MERCHANT-ID: $this->merchant_id",
        ];
        $response = $this->curl($url, $method, [], $header);
        $res = json_decode($response['body'], true);
        return $res;
    }
    public function curl($url, $method = 'POST', $data = [], $header = [])
    {
        $ch = curl_init();
        $curl_options = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HEADER => 0,
            CURLOPT_HTTPHEADER => $header
        );
        if (strtolower($method) == 'post') {
            $curl_options[CURLOPT_POST] = 1;
            if (!empty($data)) {
                $curl_options[CURLOPT_POSTFIELDS] = $data;
            }
        } else {
            $curl_options[CURLOPT_CUSTOMREQUEST] = 'GET';
        }
        curl_setopt_array($ch, $curl_options);
        $result = array(
            'body' => curl_exec($ch),
            'http_code' => curl_getinfo($ch, CURLINFO_HTTP_CODE),
        );
        return $result;
    }
}

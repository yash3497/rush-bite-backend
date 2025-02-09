<?php

class GeminiAI
{
    private $secret_key = "";
    private $url = "";

    function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->helper('url');
        $this->CI->load->helper('form');
        $system_settings = get_settings('system_settings', true);

        $this->secret_key = $system_settings['google_map_api_key'];
        $this->url = "https://maps.googleapis.com/maps/api/";
    }
    public function get_credentials()
    {
        $data['secret_key'] = $this->secret_key;
        $data['url'] = $this->url;
        return $data;
    }

   
    

    public function curl($url, $method = 'GET', $data = [])
    {
        $ch = curl_init();
        $curl_options = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HEADER => 0,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/x-www-form-urlencoded',
                'Authorization: Basic ' . base64_encode($this->secret_key . ':')
            )
        );
        if (strtolower($method) == 'post') {
            $curl_options[CURLOPT_POST] = 1;
            $curl_options[CURLOPT_POSTFIELDS] = http_build_query($data);
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

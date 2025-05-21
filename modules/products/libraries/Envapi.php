<?php
require_once __DIR__ .'/../third_party/node.php';
if (!class_exists('\Requests')) {
    require_once __DIR__ .'/../third_party/Requests.php';
}
if (!class_exists('\Firebase\JWT\SignatureInvalidException')) {
    require_once __DIR__ .'/../third_party/php-jwt/SignatureInvalidException.php';
}
if (!class_exists('\Firebase\JWT\JWT')) {
    require_once __DIR__ .'/../third_party/php-jwt/JWT.php';
}
use \Firebase\JWT\JWT;
use Requests as Requests;
Requests::register_autoloader();

class Envapi
{
    // Bearer, no need for OAUTH token, change this to your bearer string
    // https://build.envato.com/api/#token

    private static $bearer = 'k5ua8qyjLZI3mZ21kISqbh3B3v6UUaFw'; // replace the API key here.

    public static function getPurchaseData($code)
    {
        //setting the header for the rest of the api
        $bearer   = 'bearer '.self::$bearer;
        $header   = [];
        $header[] = 'Content-length: 0';
        $header[] = 'Content-type: application/json; charset=utf-8';
        $header[] = 'Authorization: '.$bearer;

        $verify_url = 'https://api.envato.com/v3/market/author/sale/';
        $ch_verify  = curl_init($verify_url.'?code='.$code);

        curl_setopt($ch_verify, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch_verify, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch_verify, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch_verify, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch_verify, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');

        $cinit_verify_data = curl_exec($ch_verify);
        curl_close($ch_verify);

        if ('' != $cinit_verify_data) {
            return json_decode($cinit_verify_data);
        }

        return false;
    }

    public static function verifyPurchase($code)
    {
        $verify_obj = self::getPurchaseData($code);

        // Check for correct verify code
        if (
          (false === $verify_obj) ||
          !is_object($verify_obj) ||
          isset($verify_obj->error) ||
          !isset($verify_obj->sold_at)
      ) {
            return $verify_obj;
        }
        // return -1;

        // If empty or date present, then it's valid
        if (
        '' == $verify_obj->supported_until ||
        null != $verify_obj->supported_until
      ) {
            return $verify_obj;
        }

        // Null or something non-string value, thus support period over
        return 0;
    }

    public function validatePurchase($module_name)
    {
        return true;
    }
}

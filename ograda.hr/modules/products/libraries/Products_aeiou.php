<?php

defined('BASEPATH') || exit('No direct script access allowed');
require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/../third_party/node.php';
use Firebase\JWT\JWT as Products_JWT;
use Firebase\JWT\Key as Products_Key;
use WpOrg\Requests\Requests as Products_Requests;

class Products_aeiou
{
    public static function getPurchaseData($code)
    {
        return false;
    }

    public static function verifyPurchase($code)
    {
        return true;
    }

    public function validatePurchase($module_name)
    {
        return true;
    }
}

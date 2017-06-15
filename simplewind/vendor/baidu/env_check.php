<?php
/**
 * Environment check for DataApi
 * Please check your php environment by env_check() method before use DataApi Demo!
 */
require_once('Utility.php');

/**
 * environment check
 * @return boolean
 */
function env_check()
{
    echo "------------------environment checking------------------" . PHP_EOL;
    //step1, shallow check : check  openssl and curl extensions

    echo "[notice] start shallow check !" . PHP_EOL;

    $extensions = get_loaded_extensions();
    if (!in_array('curl', $extensions))
    {
        echo "[error] shallow Check failed: please enable curl extension for php !" . PHP_EOL;
        return false;
    }
    if (!in_array('openssl', $extensions))
    {
        echo "[error] shallow Check failed: please enable openssl extension for php !" . PHP_EOL;
        return false;
    }
    echo "[notice] shallow check passed !" . PHP_EOL;

    //step2, function check : check used functions of openssl and curl

    echo "[notice] start function check !" . PHP_EOL;

    $func_openssl = get_extension_funcs("openssl");
    if (!in_array('openssl_pkey_get_public', $func_openssl))
    {
        echo "[error] function check failed: unknow function openssl_pkey_get_public !" . PHP_EOL;
        return false;
    }

    if (!in_array('openssl_public_encrypt', $func_openssl))
    {
        echo "[error] function check failed: unknow function openssl_public_encrypt !" . PHP_EOL;
        return false;
    }

    $func_curl = get_extension_funcs("curl");
    if (!in_array('curl_init', $func_curl))
    {
        echo "[error] function check failed: unknow function curl_init !" . PHP_EOL;
        return false;
    }

    if (!in_array('curl_setopt', $func_curl))
    {
        echo "[error] function check failed: unknow function curl_setopt !" . PHP_EOL;
        return false;
    }

    if (!in_array('curl_exec', $func_curl))
    {
        echo "[error] function check failed: unknow function curl_exec !" . PHP_EOL;
        return false;
    }

    if (!in_array('curl_error', $func_curl))
    {
        echo "[error] function check failed: unknow function curl_error !" . PHP_EOL;
        return false;
    }

    if (!in_array('curl_close', $func_curl))
    {
        echo "[error] function check failed: unknow function curl_close !" . PHP_EOL;
        return false;
    }

    if (!in_array('curl_errno', $func_curl))
    {
        echo "[error] function check failed: unknow function curl_errno !" . PHP_EOL;
        return false;
    }

    echo "[notice] function check passed !" . PHP_EOL;

    //step3, deep check: test pub encrypt and curl post indeed

    echo "[notice] start deep check !" . PHP_EOL;

    $rsa = new RsaPublicEncrypt('./');
    if (!$rsa->pubEncrypt("test pub encrypt"))
    {
        echo "[error] deep check failed: pub encrypt failed !" . PHP_EOL;
        return false;
    }

    $url = "www.baidu.com";
    $heads = array('Content-Type:  text/html;charset=UTF-8');
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 1);
    curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $heads);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, "test curl post");
    curl_setopt($curl, CURLOPT_TIMEOUT, 30);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

    $tmpInfo = curl_exec($curl);
    if (curl_errno($curl))
    {
        echo "[error] deep check failed: curl post failed !" . PHP_EOL;
        return false;
    }
    curl_close($curl);

    echo "[notice] deep check passed !" . PHP_EOL;

    echo "----------------environment checking End----------------" . PHP_EOL;
}

env_check();

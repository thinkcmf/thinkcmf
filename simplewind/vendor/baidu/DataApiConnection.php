<?php
/**
 * class DataApiConnection, provide POST method: send POST request to DataApi URL
 */
class DataApiConnection {
    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $headers;

    /**
     * @var string
     */
    private $postData;
    
    /**
     * @var string
     */
    public $retHead;
    
    /**
     * @var string
     */
    public $retBody;
    
    /**
     * @var string
     */
    public $retRaw;

    /**
     * init
     * @param string $url
     * @param string $ucid
     */
    public function init($url, $ucid) {
        $this->url = $url;
        $this->headers = array('UUID: '.UUID, 'USERID: '.$ucid, 'Content-Type:  data/json;charset=UTF-8');
    }

    /**
     * generate post data
     * @param array $data
     */
    public function genPostData($data) {
        $this->postData = json_encode($data);
    }

    /**
     * post
     * @param array $data
     */
    public function POST($data) {
        $this->genPostData($data);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $this->postData);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $tmpRet = curl_exec($curl);
        if (curl_errno($curl)) {
            echo '[error] CURL ERROR: ' . curl_error($curl) . PHP_EOL;
        }
        curl_close($curl);
        $tmpArray = json_decode($tmpRet, true);
        if (isset($tmpArray['header']) && isset($tmpArray['body'])) {
            $this->retHead = $tmpArray['header'];
            $this->retBody = $tmpArray['body'];
            $this->retRaw = $tmpRet;
        }
        else {
            echo "[error] SERVICE ERROR: {$tmpRet}" . PHP_EOL;
        }
    }
}

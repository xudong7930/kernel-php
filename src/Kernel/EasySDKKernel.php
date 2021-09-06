<?php

namespace EleMe\OpenApi\Kernel;
use EleMe\OpenApi\Kernel\ElemeConstants;
use EleMe\OpenApi\Kernel\Util\JsonUtil;


class EasySDKKernel{
    private $config;

    private $textParams;

    private $bizParams;

    private $optionalTextParams;

    private $optionalBizParams;

    public function __construct($config)
    {
        $this->config = $config;
    }
    public function getTimestamp(){
        $time = explode (" ", microtime () );
        $time = $time [1] . ($time [0] * 1000);
        $time2 = explode ( ".", $time );
        $time = $time2 [0];
        return $time;
    }

    public function getConfig($key)
    {
        return $this->config->$key;
    }

    public function toUrlEncodedRequestBody($bizParams)
    {
        $sortedMap = $this->getSortedMap(null, $bizParams, null);
        if (empty($sortedMap)) {
            return null;
        }
        return $this->buildQueryString($sortedMap);
    }
    public function readAsJson($response){
        $responseBody = (string)$response->getBody();
        return $responseBody ;
    }

    public function toRespModel($respMap){
        $code = $respMap[ElemeConstants::CODE];
        $msg = $respMap[ElemeConstants::MSG];
        if ($code == '200'){
            $data = $respMap[ElemeConstants::BIZ_CONTENT_FIELD];
            $model = json_decode($data, true);
            return $model;
        }
        throw new Exception("接口访问异常，code:".$code.",msg:".$msg);
    }

    private function buildQueryString(array $sortedMap)
    {
        $requestUrl = null;
        foreach ($sortedMap as $sysParamKey => $sysParamValue) {
            $requestUrl .= "$sysParamKey=" . urlencode($this->characet($sysParamValue, ElemeConstants::DEFAULT_CHARSET)) . "&";
        }
        $requestUrl = substr($requestUrl, 0, -1);
        return $requestUrl;

    }

    private function getSortedMap($systemParams, $bizParams, $textParams)
    {
        $this->textParams = $textParams;
        $this->bizParams = $bizParams;
        if ($textParams != null && $this->optionalTextParams != null) {
            $this->textParams = array_merge($textParams, $this->optionalTextParams);
        } else if ($textParams == null) {
            $this->textParams = $this->optionalTextParams;
        }
        if ($bizParams != null && $this->optionalBizParams != null) {
            $this->bizParams = array_merge($bizParams, $this->optionalBizParams);
        } else if ($bizParams == null) {
            $this->bizParams = $this->optionalBizParams;
        }
        $json = new JsonUtil();
        if ($this->bizParams != null) {
            $bizParams = $json->toJsonString($this->bizParams);
        }
        $sortedMap = $systemParams;
        if (!empty($bizParams)) {
            $sortedMap[ElemeConstants::BIZ_CONTENT_FIELD] = json_encode($bizParams, JSON_UNESCAPED_UNICODE);
        }
        if (!empty($this->textParams)) {
            if (!empty($sortedMap)) {
                $sortedMap = array_merge($sortedMap, $this->textParams);
            } else {
                $sortedMap = $this->textParams;
            }
        }
        return $sortedMap;
    }

    public function sign($systemParams, $bizParams, $textParams, $privateKey)
    {
        $sortedMap = $this->getSortedMap($systemParams, $bizParams, $textParams);
        $requestUrl = $this->buildQueryString($sortedMap);
        $encodeStr = $privateKey.$requestUrl;
        return hash("sha256",$encodeStr);
    }

    public function toJSONString($bizParams){
        $json = new JsonUtil();
        return $json->toJsonString($bizParams);
    }

    public function sortMap($randomMap)
    {
        return $randomMap;
    }


    /**
     * 转换字符集编码
     * @param $data
     * @param $targetCharset
     * @return string
     */
    function characet($data, $targetCharset)
    {
        if (!empty($data)) {
            $fileType = ElemeConstants::DEFAULT_CHARSET;
            if (strcasecmp($fileType, $targetCharset) != 0) {
                $data = mb_convert_encoding($data, $targetCharset, $fileType);
            }
        }
        return $data;
    }

}
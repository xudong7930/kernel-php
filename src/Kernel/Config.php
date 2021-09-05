<?php

namespace EleMe\OpenApi\Kernel;

use AlibabaCloud\Tea\Model;

class Config extends Model
{
    public $protocol;
    public $gatewayHost;
    public $appId;
    public $accessToken;
    public $merchantId;
    public $secretKey;
    public $httpProxy;
    public $ignoreSSL;

}
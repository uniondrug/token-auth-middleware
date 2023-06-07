<?php
namespace Uniondrug\TokenAuthMiddleware;

use App\Models\SaasUser;
use Uniondrug\Framework\Services\Service;

class SaasTokenService extends Service
{
    public function setLoginInfo($token)
    {
        $_SESSION['saasRole'] =  $token->customInfo['role'];
        $_SESSION['saasMobile'] =  $token->customInfo['mobile'];
        $_SESSION['saasPlatform'] =  $token->customInfo['platform'];
        if(isset($token->customInfo['storeId']))$_SESSION['saasStoreId'] = $token->customInfo['storeId'];
        if(isset($token->customInfo['merchantId']))$_SESSION['saasMerchantId'] = $token->customInfo['merchantId'];
        if(isset($token->customInfo['hospitalId']))$_SESSION['saasHospitalId'] = $token->customInfo['hospitalId'];
    }


    public function getSaasRole()
    {
        return $_SESSION['saasRole'];
    }

    public function getSaasStoreId()
    {
        return $_SESSION['saasStoreId'];
    }

    public function getSaasMerchantId()
    {
        return $_SESSION['saasMerchantId'];
    }

    public function getSaasHospitalId()
    {
        return $_SESSION['saasHospitalId'];
    }

    public function getSaasPlatform()
    {
        return $_SESSION['saasPlatform'];
    }
}
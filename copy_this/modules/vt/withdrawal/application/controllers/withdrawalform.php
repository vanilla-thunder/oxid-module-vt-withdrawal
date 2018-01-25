<?php

class withdrawalform extends oxUBase
{

    protected $_sThisTemplate = 'withdrawalform.tpl';

    public function getTitle()
    {
        return oxRegistry::getLang()->translateString( "WITHDRAWAL_FORM", oxRegistry::getLang()->getBaseLanguage(), false);
    }

    public function getBreadCrumb()
    {
        $aPaths = array();
        $aPath = array();

        $aPath['title'] = $this->getTitle();
        $aPath['link'] = 'index.php?cl=withdrawalform';
        $aPaths[] = $aPath;

        return $aPaths;
    }

    public function getUserOrders()
    {
        if(!$oUser = oxRegistry::getConfig()->getUser()) return false;

        $oOrderList = oxNew('oxList');
        $oOrderList->init('oxorder');
        $oOrderList->selectString('select * from oxorder where oxbillemail = "'.$oUser->oxuser__oxusername->value. '"');//' and oxorderdate >= "' . date('Y-m-d', strtotime('-30 days')) . '" order by oxorderdate desc');
        return ( $oOrderList->count() > 0 ? $oOrderList : false );
    }
    public function getOrderArticles()
    {
        $wdf = oxRegistry::getConfig()->getRequestParameter("wdf");
        if(!empty($wdf["oxorderid"]))
        {
            $oArticleList = oxNew("oxList");
            $oArticleList->init('oxorderarticle');
            $oArticleList->selectString('select * from oxorderarticles where oxorderid="'.$wdf["oxorderid"].'"');
            $this->addTplParam("aOrderProducts",$oArticleList);
        }
        else {
            $this->addTplParam("aOrderProducts",false);
        }
    }
    public function getWithdrawalReasons() {
        $aReasonst = oxRegistry::getConfig()->getConfigParam('vtWithdrawalReasons');
        return (!empty($aReasonst) ? $aReasonst : false);
    }

    public function getRecaptchaSiteKey()
    {
        return oxRegistry::getConfig()->getConfigParam('vtWithdrawalSitekey');
    }
    protected function _checkRecaptcha($token)
    {
        oxRegistry::getUtils()->writeToLog("recaptcha token: ".print_r($token,true),"recaptcha.log");
        if(!$token)
        {
            $this->addTplParam("submitError","1");
            return false;
        }
        $this->addTplParam("errorArgs",oxRegistry::getConfig()->getActiveShop()->oxshops__oxorderemail->value);

        $client  = @$_SERVER['HTTP_CLIENT_IP'];
        $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
        $ip = $_SERVER['REMOTE_ADDR'];
        if(filter_var($client, FILTER_VALIDATE_IP)) $ip = $client;
        elseif(filter_var($forward, FILTER_VALIDATE_IP)) $ip = $forward;

        $data = [
            'secret' => oxRegistry::getConfig()->getConfigParam("vtWithdrawalSecret"),
            'response' => $token,
            'remoteip' => $ip
        ];

        $ch = curl_init('https://www.google.com/recaptcha/api/siteverify');
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        //curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = (object) json_decode(curl_exec($ch));
        curl_close($ch);

        if( !isset($response->success) ) return true; // gar kein response. trotzdem email versenden
        elseif( $response->success === true ) return true;
        elseif( $response->success === false ) $this->addTplParam("submitError","2"); // captcha nicht bestätigt
        else $this->addTplParam("submitError","3"); // sollte eigentlich nicht passieren
        return false;
    }

    public function submitWithdrawal()
    {
        $wdf = oxRegistry::getConfig()->getRequestParameter("wdf");

        if(!oxRegistry::getConfig()->getUser() && !$this->_checkRecaptcha(oxRegistry::getConfig()->getRequestParameter("g-recaptcha-response"))) return;

        try
        {
            /** @var oxEmail $oEmail */
            $oEmail = oxNew("oxEmail");
            $x = $oEmail->sendWithdrawalRequestToOwner($wdf);
            $y = $oEmail->sendWithdrawalRequestToUser($wdf);
        }
        catch (Exception $oException)
        {
            $this->addTplParam("submitError","3");
            $this->addTplParam("errorArgs",$oEmail->getShop()->oxshops__oxorderemail->value);
        }

        if($x) $this->addTplParam("submitSuccess",true);
        else
        {
            $this->addTplParam("submitError","3");
            $this->addTplParam("errorArgs",$oEmail->getShop()->oxshops__oxorderemail->value);
        }
        //if(!$r2) oxRegistry::getUtils()->writeToLog("")
    }
}
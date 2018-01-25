<?php
class oxemailwithdrawal extends oxemailwithdrawal_parent
{
    protected $_sWithdrawalEmailTemplateHtml = "withdrawalEmailHtml.tpl";
    protected $_sWithdrawalEmailTemplatePlain = "withdrawalEmailPlain.tpl";

    protected function sendWithdrawalRequest($wdf,$toUser = false)
    {
        //$this->_clearMailer();

        $oShop = $this->_getShop();
        $this->_setMailParams($oShop);

        if($toUser)
        {
            $this->setViewData("toUser", true);
            $this->setViewData("toOwner", false);

            $this->setSubject("Widerruf Ihrer Bestellung bei ".$oShop->oxshops__oxname->getRawValue());
            $this->setRecipient($wdf["email"],$wdf["name"]);
            $this->setFrom($oShop->oxshops__oxorderemail->value, $oShop->oxshops__oxname->getRawValue());
            if( $sWithdrawalEmail = oxRegistry::getConfig()->getConfigParam("vtWithdrawalEmail") ) $this->setReplyTo($sWithdrawalEmail);
        }
        else
        {
            $this->setViewData("toUser", false);
            $this->setViewData("toOwner", true);

            $this->setSubject(" Widerruf einer Bestellung bei ".$oShop->oxshops__oxname->getRawValue());
            if( $_recipient = oxRegistry::getConfig()->getConfigParam("vtWithdrawalEmail") ) $this->setRecipient($_recipient);
            else $this->setRecipient($oShop->oxshops__oxorderemail->value, $oShop->oxshops__oxname->getRawValue());
            if( !empty(oxRegistry::getConfig()->getConfigParam("vtWithdrawalCC")) ) foreach(oxRegistry::getConfig()->getConfigParam("vtWithdrawalCC") as $_ccrecipient )  $this->addOrEnqueueAnAddress('cc', $_ccrecipient, '');
            $this->setFrom($wdf->email, $wdf->name);
        }

        $oSmarty = $this->_getSmarty();
        $this->setViewData("wdf", $wdf);
        if(oxRegistry::getConfig()->getUser())
        {
            $this->setUser(oxRegistry::getConfig()->getUser());
            if($wdf["oxorderid"]) {
                $oOrder = oxNew("oxorder");
                $oOrder->load($wdf["oxorderid"]);
                $this->setViewData("oOrder",$oOrder);
            }
        }
        $this->setViewData("retoureportal", oxRegistry::getConfig()->getConfigParam("vtWithdrawalRetoureportal"));
        $this->_processViewArray();

        $this->setBody($oSmarty->fetch($this->_sWithdrawalEmailTemplateHtml));
        $this->setAltBody($oSmarty->fetch($this->_sWithdrawalEmailTemplatePlain));

        return $this->send();
    }
    public function sendWithdrawalRequestToUser($wdf)
    {
        return $this->sendWithdrawalRequest($wdf,true);
    }
    public function sendWithdrawalRequestToOwner($wdf)
    {
        return $this->sendWithdrawalRequest($wdf,false);
    }
}
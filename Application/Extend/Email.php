<?php

/**
 * vanilla-thunder/oxid-module-withdrawal-form
 * Smart Withdrawal Form for OXID eShop v6.2+
 * Copyright (C) 2020 Marat Bedoev
 *
 * This program is free software;
 * you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation;
 * either version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 *  without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with this program; if not, see <http://www.gnu.org/licenses/>
 */

namespace VanillaThunder\WithdrawalForm\Application\Extend;

use \OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Framework\Templating\TemplateRendererBridgeInterface;

class Email extends Email_parent
{
    protected $_sWithdrawalEmailTemplateHtml = "withdrawalEmailHtml.tpl";
    protected $_sWithdrawalEmailTemplatePlain = "withdrawalEmailPlain.tpl";

    protected function sendWithdrawalRequest($wdf, $toUser = false)
    {
        $oShop = $this->_getShop();
        $this->_setMailParams($oShop);

        if ($toUser) {
            $this->setViewData("toUser", true);
            $this->setViewData("toOwner", false);

            $this->setSubject("Widerruf Ihrer Bestellung bei ".$oShop->oxshops__oxname->getRawValue());
            $this->setRecipient($wdf["email"], $wdf["name"]);
            $this->setFrom($oShop->oxshops__oxorderemail->value, $oShop->oxshops__oxname->getRawValue());
            if ($sWithdrawalEmail = Registry::getConfig()->getConfigParam("vtWithdrawalEmail")) {
                $this->setReplyTo($sWithdrawalEmail);
            }
        } else {
            $this->setViewData("toUser", false);
            $this->setViewData("toOwner", true);

            $this->setSubject(" Widerruf einer Bestellung bei ".$oShop->oxshops__oxname->getRawValue());
            if ($_recipient = Registry::getConfig()->getConfigParam("vtWithdrawalEmail")) {
                $this->setRecipient($_recipient);
            } else {
                $this->setRecipient($oShop->oxshops__oxorderemail->value, $oShop->oxshops__oxname->getRawValue());
            }
            if (!empty(Registry::getConfig()->getConfigParam("vtWithdrawalCC"))) {
                foreach (Registry::getConfig()->getConfigParam("vtWithdrawalCC") as $_ccrecipient) {
                    $this->addOrEnqueueAnAddress('cc', $_ccrecipient, '');
                }
            }
            $this->setFrom($wdf->email, $wdf->name);
        }

        $this->setViewData("wdf", $wdf);

        $oUser = Registry::getConfig()->getUser();
        if ($oUser) {
            $this->setUser($oUser);
            if ($wdf["oxorderid"]) {
                $oOrder = oxNew(\OxidEsales\Eshop\Application\Model\Order::class);
                $oOrder->load($wdf["oxorderid"]);
                $this->setViewData("oOrder", $oOrder);
            }
        }
        $this->setViewData("retoureportal", Registry::getConfig()->getConfigParam("vtWithdrawalRetoureportal"));
        $this->_processViewArray();

        // new
        //$renderer = $this->getRenderer(); // private...
        $bridge = $this->getContainer()->get(TemplateRendererBridgeInterface::class);
        $bridge->setEngine($this->_getSmarty());
        $renderer = $bridge->getTemplateRenderer();

        $this->setBody($renderer->renderTemplate($this->_sWithdrawalEmailTemplateHtml, $this->getViewData()));
        $this->setAltBody($renderer->renderTemplate($this->_sWithdrawalEmailTemplatePlain, $this->getViewData()));

        return $this->send();
    }
    public function sendWithdrawalRequestToUser($wdf)
    {
        return $this->sendWithdrawalRequest($wdf, true);
    }
    public function sendWithdrawalRequestToOwner($wdf)
    {
        return $this->sendWithdrawalRequest($wdf, false);
    }
}

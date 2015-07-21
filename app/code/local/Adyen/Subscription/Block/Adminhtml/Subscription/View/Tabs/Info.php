<?php
/**
 *               _
 *              | |
 *     __ _   _ | | _  _   ___  _ __
 *    / _` | / || || || | / _ \| '  \
 *   | (_| ||  || || || ||  __/| || |
 *    \__,_| \__,_|\__, | \___||_||_|
 *                 |___/
 *
 * Adyen Subscription module (https://www.adyen.com/)
 *
 * Copyright (c) 2015 H&O (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 *
 * Author: Adyen <magento@adyen.com>, H&O <info@h-o.nl>
 */

class Adyen_Subscription_Block_Adminhtml_Profile_View_Tabs_Info
    extends Mage_Adminhtml_Block_Sales_Order_Abstract
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    protected function _beforeToHtml()
    {
        Mage_Adminhtml_Block_Sales_Order_Abstract::_beforeToHtml();
    }

    /**
     * @return Adyen_Subscription_Model_Profile
     */
    public function getProfile()
    {
        return Mage::registry('adyen_subscription');
    }

    /**
     * @return Adyen_Payment_Model_Billing_Agreement
     */
    public function getBillingAgreement()
    {
        return $this->getProfile()->getBillingAgreement();
    }

    /**
     * @return string
     */
    public function getBillingAgreementViewUrl()
    {
        return $this->getUrl('adminhtml/sales_billing_agreement/view', array('agreement' => $this->getBillingAgreement()->getId()));
    }

    /**
     * @return string
     */
    public function getCustomerViewUrl()
    {
        return $this->getUrl('adminhtml/customer/edit', array('id' => $this->getProfile()->getCustomerId()));
    }

    /**
     * @return null|string
     */
    public function getStoreName()
    {
        if ($this->getProfile()) {
            $storeId = $this->getProfile()->getStoreId();
            if (is_null($storeId)) {
                $deleted = Mage::helper('adminhtml')->__(' [deleted]');
                return nl2br($this->getProfile()->getStoreId()) . $deleted;
            }
            $store = Mage::app()->getStore($storeId);
            $name = array(
                $store->getWebsite()->getName(),
                $store->getGroup()->getName(),
                $store->getName()
            );
            return implode('<br/>', $name);
        }
        return null;
    }

    /**
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('adminhtml')->__('Information');
    }

    /**
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('adminhtml')->__('Information');
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }
}
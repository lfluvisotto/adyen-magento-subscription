<?php
/**
 * Ho_Recurring
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the H&O Commercial License
 * that is bundled with this package in the file LICENSE_HO.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.h-o.nl/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@h-o.com so we can send you a copy immediately.
 *
 * @category    Ho
 * @package     Ho_Recurring
 * @copyright   Copyright © 2015 H&O (http://www.h-o.nl/)
 * @license     H&O Commercial License (http://www.h-o.nl/license)
 * @author      Maikel Koek – H&O <info@h-o.nl>
 */

class Ho_Recurring_Model_Service_Order extends Mage_Core_Model_Abstract
{
    /**
     * @param Mage_Sales_Model_Order $order
     * @return Ho_Recurring_Model_Profile
     */
    public function createProfile(Mage_Sales_Model_Order $order)
    {
        /** @var Ho_Recurring_Model_Profile $profile */
        $profile = Mage::getModel('ho_recurring/profile');

        $profile->setOrderId($order->getId());
        $profile->save();

        foreach ($order->getAllVisibleItems() as $orderItem) {
            /** @var Mage_Sales_Model_Order_Item $orderItem */
            /** @var Ho_Recurring_Model_Profile_Item $item */
            $item = Mage::getModel('ho_recurring/profile_item');
            $item->setProfileId($profile->getId());

            $item->setProductId($orderItem->getProductId());
            $item->setSku($orderItem->getSku());
            $item->setName($orderItem->getName());
            $item->setPrice($orderItem->getPrice());
            $item->setQty($orderItem->getQtyOrdered());
            $item->setOnce(0);
            $item->setCreatedAt(now());
            $item->setStatus($item::STATUS_ACTIVE);

            $item->save();
        }

        return $profile;
    }
}
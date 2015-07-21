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

class Adyen_Subscription_Model_Resource_Profile_Address extends Mage_Core_Model_Resource_Db_Abstract
{
    public function _construct()
    {
        $this->_init('adyen_subscription/profile_address', 'item_id');
    }

    /**
     * @param Adyen_Subscription_Model_Profile_Address $object
     * @param Adyen_Subscription_Model_Profile $profile
     * @param int $type
     * @return $this
     */
    public function loadByProfile(
        Adyen_Subscription_Model_Profile_Address $object,
        Adyen_Subscription_Model_Profile $profile,
        $type
    ) {
        $select = Mage::getResourceModel('adyen_subscription/profile_address_collection')
            ->addFieldToFilter('profile_id', $profile->getId())
            ->addFieldToFilter('type', $type)
            ->getSelect();

        $select->reset($select::COLUMNS);
        $select->columns('item_id');

        $addressId = $this->_getConnection('read')->fetchOne($select);

        $this->load($object, $addressId);

        return $this;
    }
}
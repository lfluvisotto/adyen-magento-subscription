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

class Adyen_Subscription_Model_Resource_Profile_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('adyen_subscription/profile');
    }

    /**
     * @return array
     */
    protected function _getNameFields()
    {
        $fields = array();

        $customerAccount = Mage::getConfig()->getFieldset('customer_account');
        foreach ($customerAccount as $code => $node) {
            if ($node->is('name')) {
                $fields[$code] = $code.'.value';
            }
        }
        return $fields;
    }

    /**
     * Add cutomer details(email, firstname, lastname) to select
     *
     * @return $this
     */
    public function addEmailToSelect()
    {
        $select = $this->getSelect()->joinLeft(
            ['ce' => $this->getTable('customer/entity')],
            'ce.entity_id = main_table.customer_id',
            ['customer_email' => 'email']
        );

        $customer = Mage::getResourceSingleton('customer/customer');
        foreach (array_keys($this->_getNameFields()) as $field) {
            $adapter  = $this->getConnection();
            $attr     = $customer->getAttribute($field);

            $joinExpr = $field.'.entity_id = main_table.customer_id AND '
                . $adapter->quoteInto($field.'.entity_type_id = ?', $customer->getTypeId()) . ' AND '
                . $adapter->quoteInto($field.'.attribute_id = ?', $attr->getAttributeId());

            $select->joinLeft([$field => $attr->getBackend()->getTable()], $joinExpr, [$field => 'value']);
        }

        return $this;
    }

    /**
     * Add Name to select
     *
     * @return $this
     */
    public function addNameToSelect()
    {
        $fields = $this->_getNameFields();
        $adapter = $this->getConnection();
        $concatenate = array();

        if (isset($fields['prefix'])) {
            $concatenate[] = $adapter->getCheckSql(
                '{{prefix}} IS NOT NULL AND {{prefix}} != \'\'',
                $adapter->getConcatSql(['LTRIM(RTRIM({{prefix}}))', '\' \'']),
                '\'\'');
        }
        $concatenate[] = 'LTRIM(RTRIM({{firstname}}))';
        $concatenate[] = '\' \'';
        if (isset($fields['middlename'])) {
            $concatenate[] = $adapter->getCheckSql(
                '{{middlename}} IS NOT NULL AND {{middlename}} != \'\'',
                $adapter->getConcatSql(['LTRIM(RTRIM({{middlename}}))', '\' \'']),
                '\'\'');
        }
        $concatenate[] = 'LTRIM(RTRIM({{lastname}}))';
        if (isset($fields['suffix'])) {
            $concatenate[] = $adapter
                    ->getCheckSql('{{suffix}} IS NOT NULL AND {{suffix}} != \'\'',
                $adapter->getConcatSql(['\' \'', 'LTRIM(RTRIM({{suffix}}))']),
                '\'\'');
        }

        $nameExpr = $adapter->getConcatSql($concatenate);


        $this->addExpressionFieldToSelect('name', $nameExpr, $fields);

        return $this;
    }


    /**
     * @return $this
     */
    public function addBillingAgreementToSelect()
    {
        $this->getSelect()->joinLeft(
            ['ba' => $this->getTable('sales/billing_agreement')],
            'ba.agreement_id = main_table.billing_agreement_id',
            ['ba_method_code' => 'method_code', 'ba_reference_id' => 'reference_id']
        );

        return $this;
    }

    /**
     * @return $this
     */
    public function addScheduleQuoteFilter()
    {
        $this->addFieldToFilter('status', array('in' => Adyen_Subscription_Model_Profile::getScheduleQuoteStatuses()));
        $this->getSelect()->joinLeft(
            ['profile_quote' => $this->getTable('adyen_subscription/profile_quote')],
            'main_table.entity_id = profile_quote.profile_id AND profile_quote.order_id IS NULL',
            [] // If we leave this empty, the entity_id of the main table is somehow not retrieved
        );
        $this->getSelect()->where('profile_quote.entity_id IS NULL');

        return $this;
    }


    /**
     * @return $this
     */
    public function addPlaceOrderFilter()
    {
        $this->addFieldToFilter('status', array('in' => Adyen_Subscription_Model_Profile::getPlaceOrderStatuses()));
        $this->getSelect()->joinLeft(
            ['profile_quote' => $this->getTable('adyen_subscription/profile_quote')],
            'main_table.entity_id = profile_quote.profile_id',
            ['quote_id', 'order_id']
        );

        $this->getSelect()
            ->where("scheduled_at < ?", now())
            ->where('profile_quote.order_id IS NULL')
            ->where('profile_quote.quote_id IS NOT NULL');

        return $this;
    }
}
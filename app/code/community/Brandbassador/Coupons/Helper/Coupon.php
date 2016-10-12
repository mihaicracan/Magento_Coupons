<?php
/**
 * Magento
 *
 *
 * @category    Brandbassador
 * @package     Brandbassador_Coupons
 */
class Brandbassador_Coupons_Helper_Coupon
{	
    /**
     * Check auth key 
     */
    public function validateAuthKey($request)
    {
        if (isset($request['auth_key']) && $request['auth_key'] == Mage::getConfig()->getNode('default/auth_key')) {
            return true;
        }

        return false;
    }

    /**
     * Convert a value from USD to a given currency
     */
    public function convertFromUSD($value, $currency)
    {
        $url   = "https://www.brandbassador.com/ajax/convertCurrency/" . $value . "/USD/" . $currency;
        $value = @file_get_contents($url);

        return $value; 
    }

    /**
     * Get Package version from config file
     */
    public function getVersion()
    {
        return Mage::getConfig()->getNode('modules/Brandbassador_Coupons/version');
    }

	/**
	 * Retrieve all customer groups
	 */
    public function getAllCustomerGroups()
    {
        $customerGroupsCollection = Mage::getModel('customer/group')->getCollection();
        $customerGroupsCollection->addFieldToFilter('customer_group_code', array('nlike'=>'%auto%'));

        $groups = array();
        foreach ($customerGroupsCollection as $group){
            $groups[] = $group->getId();
        }

        return $groups;
    }

    /**
     * Retrieve all websites
     */
    public function getAllWebsites()
    {
        $websites = Mage::getModel('core/website')->getCollection();
        $websiteIds = array();
        
        foreach ($websites as $website){
            $websiteIds[] = $website->getId();
        }

        return $websiteIds;
    }

    /**
     * Retrieve shop base currency code
     */
    public function getBaseCurrencyCode()
    {
        return Mage::app()->getStore()->getBaseCurrencyCode();
    }
}

<?php
/**
 * Magento Module developed by NoStress Commerce
 *
 * NOTICE OF LICENSE
 *
 * This program is licensed under the Koongo software licence (by NoStress Commerce).
 * With the purchase, download of the software or the installation of the software
 * in your application you accept the licence agreement. The allowed usage is outlined in the
 * Koongo software licence which can be found under https://docs.koongo.com/display/koongo/License+Conditions
 *
 * Any modification or distribution is strictly forbidden. The license
 * grants you the installation in one application. For multiuse you will need
 * to purchase further licences at https://store.koongo.com/.
 *
 * See the Koongo software licence agreement for more details.
 * @copyright Copyright (c) 2017 NoStress Commerce (http://www.nostresscommerce.cz, http://www.koongo.com/)
 *
 */

/**
 * Resource model for Koongo Connector profile category cache table
 *
 * @category Nostress
 * @package Nostress_Koongo
 *
 */

namespace Nostress\Koongo\Model\ResourceModel\Cache;

class Channelcategory extends \Nostress\Koongo\Model\ResourceModel\Cache
{
    const RULE_MAGENTO_CATEGORIES = 'magento_categories';
    const RULE_CHANNEL_CATEGORY = 'channel_category';

    protected $_cacheName = 'Channel category';
    protected $_mainTableAlias = self::NKCCHC;
    protected $_profileId = '';
    protected $_locale = '';
    protected $_storeId = '';
    protected $_taxonomyCode = '';
    protected $_rules = '';

    /**
     * Initialize resource model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('nostress_koongo_cache_channelcategory', 'product_id');
    }

    protected function reloadTable()
    {
        $this->cleanMainTable();
        $this->applyRules();
    }

    protected function cleanMainTable()
    {
        $this->helper->log(__("Clean nostress_koongo_cache_channelcategory records for profile #%1", $this->_profileId));
        $this->getConnection()->delete($this->getMainTable(), ['profile_id = ?' => $this->_profileId]);
    }

    protected function applyRules()
    {
        if (!isset($this->_rules) || !is_array($this->_rules)) {
            return;
        }

        foreach ($this->_rules as $rule) {
            if (isset($rule[self::RULE_MAGENTO_CATEGORIES]) && isset($rule[self::RULE_CHANNEL_CATEGORY])) {
                $magentoCategories = explode(",", $rule[self::RULE_MAGENTO_CATEGORIES]);
                $channelCategoryId = $rule[self::RULE_CHANNEL_CATEGORY];
                $select = $this->getSelectChannelCategoryProductRecordsSql($magentoCategories, $channelCategoryId, $this->_profileId, $this->_storeId);
                $this->insertRecords($select);
            }
        }
    }

    /*
     * Insert records with columns
    */
    protected function insertRecords($select)
    {
        $sql = $select->insertIgnoreFromSelect($this->getMainTable(), [ "profile_id","product_id","hash"]);
        $this->runQuery($sql, $this->getMainTable(), "Insert records. Filled columns: profile_id, product_id, hash.");
    }

    /************************************ Setters ***************************************/

    public function setProfileId($profileId)
    {
        $this->_profileId = $profileId;
    }

    public function setStoreId($storeId)
    {
        $this->_storeId = $storeId;
    }

    public function setTaxonomyCode($taxonomyCode)
    {
        $this->_taxonomyCode = $taxonomyCode;
    }

    public function setLocale($locale)
    {
        $this->_locale = $locale;
    }

    public function setRules($rules)
    {
        $this->_rules = $rules;
    }

    /************************************ Sql query builders ***************************************/

    protected function getSelectChannelCategoryProductRecordsSql($magentoCategories, $channelCategoryHash, $profileId, $storeId)
    {
        $catalogCategoryProductAlias = self::CCP;
        $catalogCategoryProduct =  $this->getTable('catalog_category_product');

        $select = $this->getEmptySelect();
        $select->from([$catalogCategoryProductAlias => $catalogCategoryProduct], [ "profile_id" => "('{$profileId}')","product_id" => "{$catalogCategoryProductAlias}.product_id","hash" => "('{$channelCategoryHash}')"]);

        $select->where($catalogCategoryProductAlias . '.category_id IN (?)', $magentoCategories);
        return $select;
    }
}

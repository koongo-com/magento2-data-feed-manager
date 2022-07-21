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
 * ResourceModel for Koongo Connector price cache table
 *
 * @category Nostress
 * @package Nostress_Koongo
 *
 */

namespace Nostress\Koongo\Model\ResourceModel\Cache;

class Review extends \Nostress\Koongo\Model\ResourceModel\Cache\Product
{
    protected $_cacheName = 'review';
    protected $_timestampFormat = \Nostress\Koongo\Model\Config\Source\Datetimeformat::STANDARD_DATETIME_SQL;
    const REVIEW_STATUS_APPROVED = 1;
    const REVIEW_ENTITY_PRODUCT = 1;

    protected $_mainTableAlias = self::NKCR;

    /**
     * Initialize resource model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('nostress_koongo_cache_review', 'product_id');
    }

    public function setTimestampFormatForSql($timestampFormat)
    {
        $this->_timestampFormat = $timestampFormat;
    }

    protected function getTimestampFormatForSql()
    {
        return $this->_timestampFormat;
    }

    protected function reloadTable()
    {
        $this->cleanMainTable();
        $this->insertRecords();
    }

    protected function cleanMainTable()
    {
        $this->helper->log(__("Clean nostress_koongo_cache_review for store #%1", $this->getStoreId()));
        $this->getConnection()->delete($this->getMainTable(), ['store_id = ?' => $this->getStoreId() ]);
    }

    /*
     * Insert records with columns min_price, price, qty
    */
    protected function insertRecords()
    {
        /*GROUP_CONCAT can be limited to 1024 -> increase limit in this session */
        //$this->runQuery('SET SESSION group_concat_max_len = 1000000;'); //Not used at the moment

        $sql = $this->getInsertRecordsSql();
        $this->runQuery($sql, $this->getMainTable(), "Insert records. Filled columns: product_id, store_id, reviews .");
    }

    /************************************ Sql query builders ***************************************/

    /*
     * Insert records
    */
    protected function getInsertRecordsSql()
    {
        $select = $this->getReviewsSelect();
        $sql = $this->getConnection()->insertFromSelect($select, $this->getMainTable(), ["product_id", "store_id","reviews"]);
        return $sql;
    }

    /*
     * Insert records with columns min_price, price, qty
     */
    protected function getReviewsSelect()
    {
        $reviewTable = $this->getTable('review');
        $reviewTableAlias = self::R;

        $reviewDetailTable = $this->getTable('review_detail');
        $reviewDetailTableAlias = self::RD;

        $subSelect = $this->getEmptySelect();
        $subSelect = $this->getEmptySelect();

        $subSelect->from([$reviewTableAlias => $reviewTable], ['product_id' => $reviewTableAlias . '.entity_pk_value','store_id' => "({$this->getStoreId()})"]);
        $subSelect->columns($this->helper->groupConcatColumns($this->getCacheColumns("review"), null, "reviews"));
        $subSelect->group($reviewTableAlias . '.entity_pk_value');
        $subSelect->where('status_id=?', self::REVIEW_STATUS_APPROVED);
        $subSelect->where('entity_id=?', self::REVIEW_ENTITY_PRODUCT);
        $defaultBaseStoreId = \Magento\Store\Model\Store::DEFAULT_STORE_ID;

        $subSelect->join(
            [$reviewDetailTableAlias => $reviewDetailTable],
            "{$reviewTableAlias}.review_id = {$reviewDetailTableAlias}.review_id " .
                "AND ({$reviewDetailTableAlias}.store_id = {$this->getStoreId()} OR {$reviewDetailTableAlias}.store_id = {$defaultBaseStoreId} OR ISNULL({$reviewDetailTableAlias}.store_id)) ",
            null
        );

        $ratingSubselectTable = $this->getSubSelectTable($this->getRatingSubselect());
        $ratingSubselectTableAlias = 'review_rating';

        $subSelect->joinLeft(
            [$ratingSubselectTableAlias => $ratingSubselectTable],
            "{$ratingSubselectTableAlias}.review_id = {$reviewTableAlias}.review_id ",
            null
        );

        return $subSelect;
    }

    protected function getRatingSubselect()
    {
        $ratingOptionVoteTable = $this->getTable('rating_option_vote');
        $ratingOptionVoteTableAlias = self::ROV;

        $subSelect = $this->getEmptySelect();
        $subSelect->from([$ratingOptionVoteTableAlias => $ratingOptionVoteTable], ['review_id','rating' => "(AVG(percent))"]);
        $subSelect->group('review_id');
        return $subSelect;
    }

    public function getCacheColumns($type = null)
    {
        $columns = parent::getCacheColumns($type);
        if ($type == "review" && isset($columns["review_created_at"])) {
            if ($this->getTimestampFormatForSql() == \Nostress\Koongo\Model\Config\Source\Datetimeformat::TIMESTAMP) {
                $columns["review_created_at"] = new \Zend_Db_Expr("UNIX_TIMESTAMP(" . self::R . ".created_at)");
            } else {
                $columns["review_created_at"] = new \Zend_Db_Expr("DATE_FORMAT(" . self::R . ".created_at,'{$this->getTimestampFormatForSql()}')");
            }
        }
        return $columns;
    }
}

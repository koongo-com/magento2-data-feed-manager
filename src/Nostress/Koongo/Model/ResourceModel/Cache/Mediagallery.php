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
 * ResourceModel for Koongo Connector media gallery cache table
 *
 * @category Nostress
 * @package Nostress_Koongo
 *
 */

namespace Nostress\Koongo\Model\ResourceModel\Cache;

use Nostress\Koongo\Model\Config\Source\Imageattributesource;

class Mediagallery extends \Nostress\Koongo\Model\ResourceModel\Cache
{
    protected $_cacheName = 'media_gallery';
    protected $_mainTableAlias = self::NKCMG;
    protected $_excludedImagesExportEnabled = "1";
    protected $_imageAttributeSource = Imageattributesource::STORE_VIEW_OR_DEFAULT;

    /**
     * Initialize resource model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('nostress_koongo_cache_mediagallery', 'product_id');
    }

    protected function reloadTable()
    {
        $this->cleanMainTable();
        $this->insertRecords();
    }

    protected function cleanMainTable()
    {
        $this->helper->log(__("Clean nostress_koongo_cache_mediagallery records for store #%1", $this->getStoreId()));
        $this->getConnection()->delete($this->getMainTable(), ['store_id = ?' => $this->getStoreId()]);
    }

    /*
     * Insert records with columns qty
    */
    protected function insertRecords()
    {
        $sql = $this->getInsertRecordsSql();
        $this->runQuery($sql, $this->getMainTable(), "Insert records. Filled columns: product_id, store_id, qty.");
    }

    public function setExcludedImagesExportEnabled($status)
    {
        if (isset($status)) {
            $this->_excludedImagesExportEnabled = $status;
        }
    }

    protected function getExcludedImagesExportEnabled()
    {
        return $this->_excludedImagesExportEnabled;
    }

    public function setImageAttributeSource($source)
    {
        if (isset($source)) {
            $this->_imageAttributeSource = $source;
        }
    }

    protected function getImageAttributeSource()
    {
        return $this->_imageAttributeSource;
    }

    /************************************ Sql query builders ***************************************/

    /*
     * Insert records with columns min_price, price, qty
     */
    protected function getInsertRecordsSql()
    {
        $catalogProductEntityTable = $this->getTable("catalog_product_entity");
        $catalogProductEntityTableAlias = self::CPE;
        $cpemgvteTable = $this->getTable("catalog_product_entity_media_gallery_value_to_entity");
        $cpemgvteTableAlias = self::CPEMGVTE;
        $cpemgTable = $this->getTable("catalog_product_entity_media_gallery");
        $cpemgTableAlias = self::CPAMG;
        $cpemgvTable = $this->getTable("catalog_product_entity_media_gallery_value");
        $cpemgvTableAliasValue = "value"; //Alias for table with normal value
        $cpemgvTableAliasDefaultValue = "defaultValue"; //Alias for table with default value(value for default admin store)
        $imageAttributeSource = $this->getImageAttributeSource();

        $productIdColumn = $cpemgvteTableAlias . ".entity_id";
        if ($this->isContentStagingAvailable()) {
            $productIdColumn = $catalogProductEntityTableAlias . ".entity_id";
        }

        $columns = [ "product_id" => $productIdColumn,
                            "store_id" => "({$this->getStoreId()})",
                            "value_id" => $cpemgvteTableAlias . ".value_id",
                            "value" => $cpemgTableAlias . ".value",
                            "label"	=> "IFNULL(IFNULL({$cpemgvTableAliasValue}.label,{$cpemgvTableAliasDefaultValue}.label),'')"];

        if ($imageAttributeSource == Imageattributesource::STORE_VIEW) {
            $columns["label"] = "IFNULL({$cpemgvTableAliasValue}.label,'')";
        } elseif ($imageAttributeSource == Imageattributesource::DEFAULT_VALUES) {
            $columns["label"] = "IFNULL({$cpemgvTableAliasDefaultValue}.label,'')";
        }

        $select = $this->getEmptySelect();
        $select->from([$cpemgvteTableAlias => $cpemgvteTable], $columns);

        //Join entity table to select proper row versions if content staging is active
        if ($this->isContentStagingAvailable()) {
            $select->join(
                [$catalogProductEntityTableAlias => $catalogProductEntityTable],
                "{$cpemgvteTableAlias}.row_id = {$catalogProductEntityTableAlias}.row_id AND ({$catalogProductEntityTableAlias}.created_in <= UNIX_TIMESTAMP() AND {$catalogProductEntityTableAlias}.updated_in > UNIX_TIMESTAMP())", //AND ({$catalogProductEntityTableAlias}.created_in <= UNIX_TIMESTAMP() AND {$catalogProductEntityTableAlias}.updated_in > UNIX_TIMESTAMP())
                    null
            );
        }

        $select->joinLeft(
            [$cpemgTableAlias => $cpemgTable],
            "{$cpemgvteTableAlias}.value_id = {$cpemgTableAlias}.value_id AND  {$cpemgTableAlias}.media_type = 'image'", //Mohlo by se filtrovat jeste podle attribte id - jako u magento 1.0 connectoru
                null
        );

        if ($imageAttributeSource == Imageattributesource::STORE_VIEW || $imageAttributeSource == Imageattributesource::STORE_VIEW_OR_DEFAULT) {
            $select->joinLeft(
                [$cpemgvTableAliasValue => $cpemgvTable],
                "{$cpemgvTableAliasValue}.value_id= {$cpemgTableAlias}.value_id AND {$cpemgvTableAliasValue}.store_id = {$this->getStoreId()}",
                null
            );
        }

        if ($imageAttributeSource == Imageattributesource::DEFAULT_VALUES || $imageAttributeSource == Imageattributesource::STORE_VIEW_OR_DEFAULT) {
            $select->joinLeft(
                [$cpemgvTableAliasDefaultValue => $cpemgvTable],
                "{$cpemgvTableAliasDefaultValue}.value_id= {$cpemgTableAlias}.value_id AND {$cpemgvTableAliasDefaultValue}.store_id = 0",
                null
            );
        }

        $select->group($cpemgTableAlias . '.value_id');

        switch ($imageAttributeSource) {
            case Imageattributesource::STORE_VIEW:
                $select->order([$productIdColumn ,$cpemgvTableAliasValue . ".position"]);
                //add condition - export only non excluded images - cpamg.disabled = 0
                if (!$this->getExcludedImagesExportEnabled()) {
                    $select->where("{$cpemgvTableAliasValue}.disabled = ?", 0);
                }
                break;
            case Imageattributesource::DEFAULT_VALUES:
                $select->order([$productIdColumn ,$cpemgvTableAliasDefaultValue . ".position"]);
                //add condition - export only non excluded images - cpamg.disabled = 0
                if (!$this->getExcludedImagesExportEnabled()) {
                    $select->where("{$cpemgvTableAliasDefaultValue}.disabled = ?", 0);
                }
                break;
            default:
                $select->order([$productIdColumn ,$cpemgvTableAliasValue . ".position",$cpemgvTableAliasDefaultValue . ".position"]);
                //add condition - export only non excluded images - cpamg.disabled = 0
                if (!$this->getExcludedImagesExportEnabled()) {
                    $select->where("{$cpemgvTableAliasValue}.disabled = ?", 0);
                    //Default value is not loaded anymore
                    $select->orWhere("{$cpemgvTableAliasValue}.disabled IS NULL AND {$cpemgvTableAliasDefaultValue}.disabled = 0");
                }
                break;
        }
        $sql = $this->getConnection()->insertFromSelect($select, $this->getMainTable(), array_keys($columns));
        return $sql;
    }
}

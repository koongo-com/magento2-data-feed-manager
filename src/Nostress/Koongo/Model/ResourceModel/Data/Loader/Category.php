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
 * Data loader resource model for export process
* @category Nostress
* @package Nostress_Koongo
*
*
*/

namespace Nostress\Koongo\Model\ResourceModel\Data\Loader;

use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;
use Nostress\Koongo\Helper\Data\Loader;

class Category extends \Nostress\Koongo\Model\ResourceModel\Data\Loader
{
    const ZERO_LEVEL = 0;

    /**
     * Initialize collection select
     * Redeclared for remove entity_type_id condition
     * in catalog_product_entity we store just products
     *
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
     */
    public function init()
    {
        parent::init();
        $select = $this->getSelect();
        $mainTableAlias = $this->getMainTable(true);
        $select->from([$mainTableAlias => $this->getMainTable()], $this->getColumns($mainTableAlias));
        $select->distinct();
        if (!$this->getData(self::ALLOW_INACTIVE_CATEGORIES_EXPORT, "1")) {
            $select->where($mainTableAlias . '.is_active=?', self::CATEGORY_ACTIVE);
        }
        $select->where($mainTableAlias . '.level>=?', $this->getCategoryLowestLevel());
        return $this;
    }

    public function getMainTable($alias = false)
    {
        return $this->getCategoryFlatTable($alias);
    }

    protected function defineColumns()
    {
        $defaultCatPathDelim = self::DEF_CATEGORY_PATH_DELIMITER;

        parent::defineColumns();
        $this->_columns[$this->getCategoryFlatTable(true)] = [ "id" => "entity_id",
                                                                    "name" => "name",
                                                                      "path_ids" => "(SUBSTRING_INDEX({$this->getCategoryFlatTable(true)}.path,'{$defaultCatPathDelim}',-{$this->getCategoryFlatTable(true)}.level+{$this->getCategoryLowestLevel(true)}))",
                                                                     "level" => "({$this->getCategoryFlatTable(true)}.level - {$this->getCategoryLowestLevel()})",
                                                                     "parent_id" => "parent_id",
                                                                    "url_key" => "url_key",
                                                                    "path_url_key" => "(REPLACE(REPLACE(IFNULL({$this->getCategoryFlatTable(true)}.url_path,''), '.html',''),'" . self::DEF_CATEGORY_PATH_DELIMITER . "','-'))",
                                                                    "meta_description" => "meta_description",
                                                                    "meta_title" => "meta_title",
                                                                    "meta_keywords" => "meta_keywords",
                                                                    "description" => "description"
        ];

        $this->_columns[self::NKCCP] = ["path" => "category_path",
                                               "root_name" => "category_root_name",
                                            "root_id" => "category_root_id"];

        /* Prepare category url link*/
        $categoryUrlSuffix = $this->getStoreConfig(CategoryUrlPathGenerator::XML_PATH_CATEGORY_URL_SUFFIX);

        if (!empty($categoryUrlSuffix)) {
            $firstChar = substr($categoryUrlSuffix, 0, 1);
            if ($firstChar != "." && $firstChar != "/") {
                $categoryUrlSuffix = "." . $categoryUrlSuffix;
            }
        }

        //url_path probably do not include .html suffix
        $this->_columns[$this->getCategoryFlatTable(true)]["url"] = "(CONCAT('{$this->getStore()->getBaseUrl()}',IFNULL({$this->getCategoryFlatTable(true)}.url_path,{$this->getCategoryFlatTable(true)}.url_key),'{$categoryUrlSuffix}'))";

        $userCatPathDelim = $this->getCategoryPathDelimiter();
        if ($userCatPathDelim !== $defaultCatPathDelim) {
            $this->_columns[self::NKCCP]["path"] = "REPLACE(" . self::NKCCP . ".category_path,'{$defaultCatPathDelim}','{$userCatPathDelim}')";
        }

        $this->_columns[$this->getCategoryFlatTable(true, null, true)] = ["parent_name" => "name"];
        //$this->dispatchDefineColumnsEvent("_category");
    }
    //*************************************** BASE PART ******************************************

    /**
    * Joint product filter
    */
    public function joinProductFilter()
    {
        $this->joinExportCategoryProduct();
    }

    /**
     * Order by category level
     */
    public function orderByLevel()
    {
        $select = $this->getSelect();
        $select->order($this->getMainTable(true) . ".level");
    }

    public function addCategoryFilter()
    {
        $categoryIds = $this->getCondition(Loader::CONDITION_CATEGORY_IDS, []);
        if (empty($categoryIds)) {
            return $this;
        } elseif (!is_array($categoryIds)) {
            $categoryIds = explode(",", $categoryIds);
        }

        $select = $this->getSelect();
        $where = $select->getAdapter()->quoteInto($this->getCategoryFlatTable(true) . ".entity_id IN(?)", $categoryIds);
        $select->where($where);

        return $this;
    }

    //**********************************COMMON PART*****************************************************

    protected function getColumns($tableAlias, $defualt = null, $groupConcat = false, $addTablePrefix = false)
    {
        if (array_key_exists($tableAlias, $this->_columns)) {
            $result = $this->_columns[$tableAlias];
        } else {
            $result = $defualt;
        }

        if ($groupConcat) {
            $result = $this->groupConcatColumns($result);
        }
        if ($addTablePrefix) {
            $result = $this->addTablePrefix($tableAlias, $result);
        }
        return $result;
    }

    public function getCategoryLowestLevel($modify = false)
    {
        $level = $this->getData(self::CATEGORY_LOWEST_LEVEL);

        if (empty($level)) {
            $level = self::ZERO_LEVEL;
        }

        if (!$modify) {
            return $level;
        } else {
            $level--;
            if ($level < 0) {
                $level = 0;
            }
            return $level;
        }
    }
}

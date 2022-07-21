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
 * ResourceModel for Koongo Connector taxonomy cateogry
 *
 * @category Nostress
 * @package Nostress_Koongo
 *
 */

namespace Nostress\Koongo\Model\ResourceModel\Taxonomy;

class Category extends \Nostress\Koongo\Model\ResourceModel\AbstractResourceModel
{
    const TAXONOMY_CODE = 'taxonomy_code';
    const LOCALE = 'locale';
    const LIMIT = 100;
    const COUNT_FIELD = "COUNT(*)";

    /**
     * Initialize resource model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('nostress_koongo_taxonomy_category', 'entity_id');
    }

    public function insertTaxonomyCategoryRecords($engineCode, $locale, $records)
    {
        $emptyRecord = [self::LOCALE=>$locale, self::TAXONOMY_CODE => $engineCode];
        $tableRows = [];

        $i = 0;
        $columns = null;

        foreach ($records as $row) {
            if (isset($row['path'])) {
                $row['hash'] = $this->getTaxonomyPathHash($row['path']);
            } else {
                throw new \Exception(__("Category with id %1 is missing path.", isset($row['id']) ? $row['id'] : "N/A"));
            }

            $i++;

            //Prepare values array
            $row = array_merge($emptyRecord, $row);
            $values = $this->addEscapeChars(array_values($row));
            $values = implode("','", $values);
            $tableRows[] = "('{$values}')";
            if (empty($columns)) {
                $columns = implode(",", array_keys($row));
            }

            if ($i > self::LIMIT) {
                $this->insertRecordsQuery($tableRows, $columns);
                $tableRows = [];
                $i = 0;
            }
        }
        if (!empty($tableRows)) {
            $this->insertRecordsQuery($tableRows, $columns);
        }
    }

    protected function getTaxonomyPathHash($path)
    {
        return hash("md5", $path);
    }

    protected function insertRecordsQuery($values, $columns)
    {
        $valuesString =  implode(", ", $values);
        $sql = "INSERT INTO {$this->getMainTable()} ({$columns}) VALUES {$valuesString} ;";
        $this->runQuery($sql, "", "", false);
    }

    protected function addEscapeChars($items)
    {
        foreach ($items as $key => $item) {
            $items[$key] = str_replace("'", "\\'", $item);
        }
        return $items;
    }

    public function cleanTable($taxonomyCode = null, $locale = null)
    {
        $where = '';
        if (!empty($taxonomyCode)) {
            $where = "taxonomy_code = '{$taxonomyCode}'";
        }

        if (!empty($locale)) {
            if (!empty($where)) {
                $where.= " AND ";
            }
            $where .= "locale = '{$locale}'";
        }

        $this->runQuery($this->deleteRecordQuery($where));
    }

    protected function deleteRecordQuery($where='')
    {
        $sql = "DELETE FROM " . $this->getMainTable();
        if ($where != '') {
            $sql .= ' WHERE ' . $where;
        }
        $sql .= ";";
        return $sql;
    }

    public function countColumns($engineCode, $locale)
    {
        $result = $this->runOneRowSelectQuery($this->countColumnsQuery($engineCode, $locale));
        if (array_key_exists(self::COUNT_FIELD, $result)) {
            return $result[self::COUNT_FIELD];
        } else {
            return 0;
        }
    }

    protected function countColumnsQuery($engineCode, $locale)
    {
        $sql = "SELECT " . self::COUNT_FIELD . " FROM {$this->getMainTable()} WHERE taxonomy_code = '{$engineCode}' AND locale = '{$locale}';";
        return $sql;
    }
}

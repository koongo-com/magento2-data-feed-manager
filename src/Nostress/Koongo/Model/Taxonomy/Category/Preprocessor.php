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
* Class for taxonomy category preprocessor
*
* @category Nostress
* @package Nostress_Koongo
*
*/

namespace Nostress\Koongo\Model\Taxonomy\Category;

class Preprocessor extends \Nostress\Koongo\Model\AbstractModel
{
    const INPUT_COLUMNS = 'input_columns';
    const CAT_PATH_DELIMITER = 'category_path_delimiter';

    const CAT_NAME = 'name';
    const CAT_ID = 'id';
    const CAT_PATH = 'path';
    const CAT_IDS_PATH = 'ids_path';
    const CAT_LEVEL = 'level';
    const CAT_PARENT_NAME = 'parent_name';
    const CAT_PARENT_ID = 'parent_id';
    const CAT_CODE1 = 'code1';
    const CAT_CODE2 = 'code2';

    const UNSELECTABLE_CATEGORY_ID = "-1";
    const DEF_OUT_DELIMITER = ' > ';

    protected $_inputCols = [];
    protected $_outputCols = [self::CAT_NAME,self::CAT_ID,self::CAT_PATH,self::CAT_IDS_PATH,self::CAT_LEVEL,self::CAT_PARENT_NAME,self::CAT_PARENT_ID,self::CAT_CODE1, self::CAT_CODE2];
    protected $_catPathDelim = '>';

    protected $_record = [];
    protected $_records = [];
    protected $_catIndexTable = [];
    protected $_categoryIdCounter = 0;
    protected $_addCategoryId = true;

    public function init($params)
    {
        //reset values
        unset($this->_records);
        unset($this->_record);
        unset($this->_catIndexTable);

        $this->_inputCols = [];
        $this->_categoryIdCounter = 0;
        $this->_addCategoryId = true;

        $this->initParam($this->_inputCols, $params[self::INPUT_COLUMNS]);
        $this->_inputCols = explode(",", $this->_inputCols);
        if (in_array(self::CAT_ID, $this->_inputCols)) {
            $this->_addCategoryId = false;
        }

        $this->initParam($this->_catPathDelim, $params[self::CAT_PATH_DELIMITER]);
    }

    public function processRecords($records, $params = null)
    {
        if (isset($params)) {
            $this->init($params);
        }
        $this->_processRecords($records);
        return $this->_postProcess();
    }

    public function _processRecords($records)
    {
        foreach ($records as $row) {
            $this->processRow($row);
        }
    }

    protected function _postProcess()
    {
        $missingColumns = null;
        foreach ($this->_catIndexTable as &$row) {
            $missingColumns = array_diff($this->_outputCols, array_keys($row));
            foreach ($missingColumns as $col) {
                $row[$col] = $this->processMissingColumn($col, $row);
            }
        }
        return $this->_catIndexTable;
    }

    protected function processRow($row)
    {
        if (!isset($row)) {
            return;
        }
        if (!is_array($row)) {
            $row = [$row];
        }

        $this->initRecord();

        $oldColumnType = "";
        $recordAdded = false;
        foreach ($this->_inputCols as $colIndex => $colType) {
            if ($colType == self::CAT_NAME && empty($row[$colIndex])) {
                $row[$colIndex] = $this->getCatIdIndexedRecordColumnValue($this->getRecordItem(self::CAT_ID), self::CAT_NAME);
            }

            if (empty($row[$colIndex])) {
                continue;
            }

            $recordAdded = false;
            if ($oldColumnType == $colType) {
                $this->addRecord();
                $recordAdded = true;
            }

            $this->processColumn($colType, $row[$colIndex]);

            if ($oldColumnType == "") {
                $oldColumnType = $colType;
            }
        }

        if (!$recordAdded) {
            $this->addRecord();
        }
    }

    protected function addRecord()
    {
        $this->parseCategoryPath();
        $name = $this->getRecordItem(self::CAT_NAME);
        if ($name === false) {
            throw new \Exception("Missing taxonomy category name.");
        }
        $this->assignCategoryId();
        if (!isset($this->_records[$name])) {
            $this->_records[$name] =  $this->_record;
        }
        $id = $this->getRecordItem(self::CAT_ID);
        if ($id && !isset($this->_records[$id])) {
            $this->_catIndexTable[$id] =  $this->_record;
        }
    }

    protected function processColumn($index, $value)
    {
        $value = trim($value);
        switch ($index) {
            case self::CAT_NAME:
                $this->addCategoryToPath($value);
                break;
            default:
                break;
        }
        $this->setRecordItem($index, $value);
    }

    protected function parseCategoryPath()
    {
        $this->_parseCategoryPath($this->getRecordItem(self::CAT_PATH));
    }

    protected function addCategoryToPath($name)
    {
        $path = $this->getRecordItem(self::CAT_PATH);
        if (!isset($path) || empty($path)) {
            $path = $this->getRecordColumnValue($name, self::CAT_PATH);
            if (empty($path)) {
                $path = $name;
            }
        } elseif ($path != $name) {
            $path .= self::DEF_OUT_DELIMITER . $name;
        }
        $this->setRecordItem(self::CAT_PATH, $path);
    }

    protected function _parseCategoryPath($categoryPath, $setLevel = true, $setName = true, $setParentName = true)
    {
        $result = [];
        $categories = $this->explodeCategoryPath($categoryPath);
        if ($setLevel) {
            $this->setRecordItem(self::CAT_LEVEL, count($categories));
        }

        $name = array_pop($categories);
        if ($setName) {
            $this->setRecordItem(self::CAT_NAME, $name);
        }

        if ($setParentName) {
            $parentName = "";
            if (!empty($categories)) {
                $parentName = array_pop($categories);
            }
            $this->setRecordItem(self::CAT_PARENT_NAME, $parentName);
        }
    }

    protected function explodeCategoryPath($categoryPath)
    {
        $path = explode($this->_catPathDelim, $categoryPath);
        if (!isset($path) || !is_array($path)) {
            $path = [];
        }

        foreach ($path as $key => $part) {
            $path[$key] = $this->trimValue($part);
        }
        return $path;
    }

    protected function trimValue($value)
    {
        return trim($value);
    }

    protected function setRecordItem($index, $value)
    {
        //if(!isset($this->_record[$index]))
        $this->_record[$index] = $value;
    }

    protected function getRecordItem($index)
    {
        if (isset($this->_record[$index])) {
            return $this->_record[$index];
        } else {
            return false;
        }
    }

    protected function initRecord()
    {
        //unset($this->_record);
        $this->_record = [];
    }

    protected function assignCategoryId()
    {
        if ($this->_addCategoryId) {
            $this->setRecordItem(self::CAT_ID, $this->_categoryIdCounter);
            $this->_categoryIdCounter++;
        }
    }

    protected function processMissingColumn($index, $row)
    {
        $value = null;
        switch ($index) {
            case self::CAT_PARENT_ID:
                $value = $this->getRecordColumnValue($row[self::CAT_PARENT_NAME], self::CAT_ID);
                break;
            case self::CAT_PARENT_NAME:
                $value = $this->getCatIdIndexedRecordColumnValue($row[self::CAT_PARENT_ID], self::CAT_NAME);
                break;
            case self::CAT_PATH:
            case self::CAT_IDS_PATH:
            case self::CAT_LEVEL:
            case self::CAT_CODE1:
            case self::CAT_CODE2:
                //TODO
                $value = "";
                break;
            case self::CAT_ID:
                $value = self::UNSELECTABLE_CATEGORY_ID;
                // no break
            default:
                break;
        }
        return $value;
    }

    protected function getRecordColumnValue($recordIndex, $columnIndex)
    {
        if (isset($this->_records[$recordIndex][$columnIndex])) {
            return $this->_records[$recordIndex][$columnIndex];
        } else {
            return false;
        }
    }

    protected function getCatIdIndexedRecordColumnValue($categoryIndex, $columnIndex)
    {
        if (isset($this->_catIndexTable[$categoryIndex][$columnIndex])) {
            return $this->_catIndexTable[$categoryIndex][$columnIndex];
        } else {
            return false;
        }
    }

    protected function initParam(&$param, $value)
    {
        if (isset($value) && !empty($value)) {
            $param = $value;
        }
    }
}

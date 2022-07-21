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

namespace Nostress\Koongo\Helper\Data;

/**
 * Main Koongo connector Helper.
 *
 * @category Nostress
 * @package Nostress_Koongo
 *
 */
class Loader extends \Nostress\Koongo\Helper\Data
{
    const GROUP_ROW_SEPARATOR = ";;";
    const GROUP_ROW_ITEM_SEPARATOR = "||";
    const GROUP_CONCAT = "GROUP_CONCAT";
    const GROUP_SEPARATOR = "SEPARATOR";
    const GROUPED_COLUMN_ALIAS = "concat_colum";

    const CONDITION_EXPORT_OUT_OF_STOCK = 'export_out_of_stock';
    const CONDITION_ATTRIBUTE_FILTER_CONDITIONS = 'conditions';
    const CONDITION_CATEGORY_IDS = 'categories';
    const CONDITION_PARENTS_CHILDS = 'parents_childs';
    const CONDITION_TYPES = 'types';
    const CONDITION_VISIBILITY = 'visibility';
    const CONDITION_VISIBILITY_PARENT = 'visibility_parent';
    const DISABLED = "disabled";

    public function groupConcatColumns($columns, $groupRowSeparator = null, $groupedColumnAlias = null)
    {
        if (!isset($groupRowSeparator)) {
            $groupRowSeparator = $this->getGroupRowSeparator();
        }

        if (!isset($groupedColumnAlias)) {
            $groupedColumnAlias = self::GROUPED_COLUMN_ALIAS;
        }

        $res = self::GROUP_CONCAT . "(";
        $columnValues = array_values($columns);

        $columnString = "";
        $separator = $this->getGroupRowItemSeparator();
        foreach ($columnValues as $value) {
            if (empty($columnString)) {
                $columnString = $value;
            } else {
                $columnString .= ",'{$separator}'," . $value;
            }
        }
        $res .= $columnString . " " . self::GROUP_SEPARATOR . " '{$groupRowSeparator}'";

        $res .= ") as " . $groupedColumnAlias;
        return new \Zend_Db_Expr($res);
    }

    protected function getGroupRowSeparator()
    {
        return self::GROUP_ROW_SEPARATOR;
    }

    protected function getGroupRowItemSeparator()
    {
        return self::GROUP_ROW_ITEM_SEPARATOR;
    }

    public function getDefaultTaxCountry($storeId)
    {
        return $this->getStoreConfig($storeId, \Magento\Tax\Model\Config::CONFIG_XML_PATH_DEFAULT_COUNTRY);
    }

    public function getDefaultTaxRegion($storeId)
    {
        return $this->getStoreConfig($storeId, \Magento\Tax\Model\Config::CONFIG_XML_PATH_DEFAULT_REGION);
    }

    /***************************** Price helpers ***********************************/
    /**
     * Rounds only complete result - no particular subresults.
     * @param unknown_type $columnName
     * @param unknown_type $taxRateColumnName
     * @param unknown_type $currencyRate
     * @param unknown_type $originalPriceIncludeTax
     * @param unknown_type $calcPriceIncludeTax
     * @param unknown_type $round
     * @return Ambigous <string, unknown>
     */
    public function getWeeeColumnFormat($columnName, $taxRateColumnName, $currencyRate = null, $originalPriceIncludeTax=false, $calcPriceIncludeTax = true, $round=true)
    {
        $resSql = $columnName;

        if (!$originalPriceIncludeTax && $calcPriceIncludeTax) {
            $resSql .= "*(1+ IFNULL(" . $taxRateColumnName . ",0))";
        } elseif ($originalPriceIncludeTax && !$calcPriceIncludeTax) {
            $resSql .= "*(1/(1+ IFNULL(" . $taxRateColumnName . ",0)))";
        }

        if (isset($currencyRate) && is_numeric($currencyRate)) {
            $resSql .= "*" . $currencyRate;
        }

        if ($round) {
            $resSql = $this->getRoundSql($resSql);
        }

        return $resSql;
    }

    /**
     * Rounds result to 2 decimal places after every currency conversion
     * @param unknown_type $columnName
     * @param unknown_type $taxRateColumnName
     * @param unknown_type $currencyRate
     * @param unknown_type $originalPriceIncludeTax
     * @param unknown_type $calcPriceIncludeTax
     * @param unknown_type $round
     * @param unknown_type $weeeColumnTaxable
     * @param unknown_type $weeeColumnNonTaxable
     * @return Ambigous <string, unknown>
     */
    public function getPriceColumnFormat($columnName, $taxRateColumnName, $currencyRate = null, $originalPriceIncludeTax=false, $calcPriceIncludeTax = true, $round=true, $weeeColumnTaxable = "", $weeeColumnNonTaxable = "")
    {
        $canConvert = false;
        if (isset($currencyRate) && is_numeric($currencyRate)) {
            $canConvert = true;
        }

        $resSql = $columnName;
        //convert and round to target currency
        if ($canConvert) {
            $resSql .= "*" . $currencyRate;
            if ($round) {
                $resSql = $this->getRoundSql($resSql);
            }
        }

        //add Fixed Product Tax - taxable
        if (!empty($weeeColumnTaxable)) {
            if ($canConvert) {
                $weeeColumnTaxable .= "*" . $currencyRate;
                if ($round) {
                    $weeeColumnTaxable = $this->getRoundSql($weeeColumnTaxable);
                }
            }

            $resSql = "(({$resSql})+{$weeeColumnTaxable})";
        }

        if (!$originalPriceIncludeTax && $calcPriceIncludeTax) {
            $resSql .= "*(1+ IFNULL(" . $taxRateColumnName . ",0))";
        } elseif ($originalPriceIncludeTax && !$calcPriceIncludeTax) {
            $resSql .= "*(1/(1+ IFNULL(" . $taxRateColumnName . ",0)))";
        }

        //add Fixed Product Tax - non-taxable
        if (!empty($weeeColumnNonTaxable)) {
            if ($canConvert) {
                $weeeColumnNonTaxable .= "*" . $currencyRate;
                if ($round) {
                    $weeeColumnNonTaxable = $this->getRoundSql($weeeColumnNonTaxable);
                }
            }
            $resSql = "(({$resSql})+{$weeeColumnNonTaxable})";
        }

        if ($round) {
            $resSql = $this->getRoundSql($resSql);
        }

        return $resSql;
    }

    public function getRoundSql($column, $decimalPlaces = 2)
    {
        return "ROUND(" . $column . ",{$decimalPlaces})";
    }
}

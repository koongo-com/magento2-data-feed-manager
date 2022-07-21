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
* File reader
* @category Nostress
* @package Nostress_Koongo
*/
namespace Nostress\Koongo\Model\Data\Reader\Common;

class Csv extends Text
{
    const DELIMITER = 'delimiter';
    protected $_columnDelimiter = "|";

    protected function initParam(&$param, $value)
    {
        if (isset($value) && !empty($value)) {
            $param = $value;
        }
    }

    protected function initParams($params)
    {
        $this->initParam($this->_columnDelimiter, $params[self::DELIMITER]);
    }
    /**
     * Returns one record from file as array
     */
    public function getRecord()
    {
        if (isset($this->_handle)) {
            return fgetcsv($this->_handle, null, $this->_columnDelimiter);
        } else {
            return false;
        }
    }
}

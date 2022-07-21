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
* ResourceModel for Koongo Connector
*
* @category Nostress
* @package Nostress_Koongo
*
*/

namespace Nostress\Koongo\Model\ResourceModel;

abstract class AbstractResourceModel extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    const TIME = 'time';
    const DATE = 'date';
    const DATE_TIME = 'date_time';

    protected function runQuery($queryString, $tableName = "", $message = "", $useTransaction = true)
    {
        if (!empty($message)) {
            $this->helper->log("Table: {$tableName}");
            $this->helper->log($message);
            //$this->helper->log($queryString);
            $this->helper->log(base64_encode($queryString));
        }

        if (!isset($queryString) || $queryString == "") {
            return $this;
        }

        if ($useTransaction) {
            $this->transactionManager->start($this->getConnection());
        }

        try {

            /* Magento\Framework\DB\Adapter\AdapterInterface*/
            $this->getConnection()->query($queryString);
            if ($useTransaction) {
                $this->commit();
            }
        } catch (\Exception $e) {
            $this->helper->log("Mysql error message: {$e->getMessage()}");
            if ($useTransaction) {
                $this->transactionManager->rollBack();
            }
            throw $e;
        }
        return $this;
    }

    protected function runSelectQuery($select, $tableName = "", $message = "")
    {
        if (!empty($message)) {
            $queryString = $select->__toString();
            $this->helper->log("Table: {$tableName}");
            $this->helper->log($message);
            //$this->helper->log($queryString);
            $this->helper->log(base64_encode($queryString));
        }
        /* Magento\Framework\DB\Adapter\AdapterInterface*/
        return $this->getConnection()->fetchAll($select);
    }

    protected function runOneRowSelectQuery($queryString)
    {
        /* Magento\Framework\DB\Adapter\AdapterInterface*/
        return $this->getConnection()->fetchRow($queryString);
    }
}

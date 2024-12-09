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
namespace Nostress\Koongo\Model\Data\Reader;

use Nostress\Koongo\Helper\Data;

class Common
{
    protected $_recordField = [];
    protected $_handle;

    protected Data $helper;

    public function __construct(Data $helper)
    {
        $this->helper = $helper;
    }

    public function openFile($filename, $params = [])
    {
        $this->initParams($params);
        try {
            $this->_handle = $this->helper->fileOpen($filename, 'r');
        } catch (\Exception $e) {
            throw new \Exception(__("Can't open file {$filename} for reading. Error: " . $e->getMessage()));
        }

        if ($this->_handle !== false) {
            return true;
        } else {
            throw new \Exception(__("Can't open file {$filename} for reading."));
        }
    }

    protected function initParams($params)
    {
    }

    /**
     * Returns one record from file as array
     */
    public function getRecord()
    {
        if (isset($this->_handle)) {
            return $this->driver->fileReadLine($this->_handle, 0);
        } else {
            return false;
        }
    }

    public function closeFile()
    {
        if (isset($this->_handle)) {
            $this->driver->fileClose($this->_handle);
        }
    }
}

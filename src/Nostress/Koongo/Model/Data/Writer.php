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
* File writer
* @category Nostress
* @package Nostress_Koongo
*/
namespace Nostress\Koongo\Model\Data;

class Writer extends \Nostress\Koongo\Model\AbstractModel
{
    const DEF_OPEN_MODE = "w";

    public function saveData($data)
    {
        $this->write($data);

        if ($this->getCompressFile()) {
            $this->compress();
        }
    }

    public function write($data)
    {
        $fp = $this->openFile($this->getFullFilename());
        fwrite($fp, $data);
        $this->closeFile($fp);
    }

    protected function openFile($filename)
    {
        $fp = fopen($filename, self::DEF_OPEN_MODE);
        if ($fp===false) {
            $e = error_get_last();
            $this->logAndException(__("Unable to open the file %1 (%2)", $filename, $e['message']));
        }
        return $fp;
    }

    /**
    * Close file and reset file pointer
    */
    protected function closeFile($fp)
    {
        if (!$fp) {
            return;
        }
        fclose($fp);
    }

    protected function compress()
    {
        $zip = $this->getZipFilename();
        $f = $this->getFullFilename();

        if ($this->helper->createZip([$this->getFilename() => $this->getFullFilename()], $this->getZipFilename(), true)) {
            //$this->helper->deleteFile($this->getFullFilename());
        }
    }
}

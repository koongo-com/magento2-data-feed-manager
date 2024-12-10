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
* Export profile main class
*
* @category Nostress
* @package Nostress_Koongo
*
*/

namespace Nostress\Koongo\Model\Io;

use Magento\Framework\Filesystem\DriverInterface;

class Sftp extends \Magento\Framework\Filesystem\Io\Sftp implements Listable
{
    protected DriverInterface $driver;

    public function __construct(DriverInterface $driver)
    {
        $this->driver = $driver;
        parent::__construct();
    }

    /**
     * Write a file
     *
     * @param string $filename
     * @param string $source string data or local file name
     * @param int $mode ignored parameter
     * @return bool
     */
    public function write($filename, $source, $mode = null)
    {
        $mode = $this->driver->isReadable($source) ? 1 : 2;
        return $this->_connection->put($filename, $source, $mode);
    }

    public function getItems()
    {
        $list = $this->rawls();
        if (!$list) {
            return false;
        }
        foreach ($list as $name => &$row) {
            if ($name == '.') {
                unset($list[$name]);
            }

            // file is if type == 1 or size != 4096
            $isFile = isset($row['type']) ? ($row['type'] == 1) : ($row['size'] != 4096);

            $row['type'] = $isFile ? self::FILE : self::DIR;
            if ($row['type'] == self::DIR) {
                $row['size'] = null;
            }
            $row['mtime'] = date('Y-m-d', $row['mtime']);
            $row['atime'] = date('Y-m-d', $row['atime']);
        }

        return $list;
    }
}

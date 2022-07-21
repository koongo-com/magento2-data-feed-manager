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

class Ftp extends \Magento\Framework\Filesystem\Io\Ftp implements Listable
{
    public function getItems()
    {
        if (is_array($children = ftp_rawlist($this->_conn, '.'))) {
            $items = [
                '..' => [
                    'type' => self::DIR,
                    'path' => dirname($this->pwd())
                 ]
            ];

            foreach ($children as $child) {
                $chunks = preg_split("/\s+/", $child);
                list($item['permissions'], $item['number'], $item['uid'], $item['gid'], $item['size'], $item['month'], $item['day'], $item['time']) = $chunks;
                $item['type'] = str_starts_with($chunks[0], 'd') ? self::DIR : self::FILE;

                array_splice($chunks, 0, 8);

                if (strpos($item['time'], ':') !== false) {
                    $item['year'] = date('Y');
                } else {
                    $item['year'] = $item['time'];
                    $item['time'] = null;
                }
                $item['mtime'] = $item['day'] . ". " . $item['month'] . " " . $item['year'];
                if ($item['time']) {
                    $item['mtime'] .= " " . $item['time'];
                }

                $items[implode(" ", $chunks)] = $item;
            }

            return $items;
        }

        // Throw exception or return false < up to you
        return false;
    }
}

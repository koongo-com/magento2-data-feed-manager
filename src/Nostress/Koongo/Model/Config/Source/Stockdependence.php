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
 * Config source model - stock dependence format
*
* @category Nostress
* @package Nostress_Koongo
*
*/
namespace Nostress\Koongo\Model\Config\Source;

class Stockdependence extends \Nostress\Koongo\Model\Config\Source
{
    const STOCK_AND_QTY = 'stock_and_qty';
    const QTY = 'qty';
    const STOCK = 'stock';

    public function toOptionArray()
    {
        return [
                ['value'=> self::STOCK, 'label'=> __('Stock status only')],
                ['value'=> self::STOCK_AND_QTY, 'label'=> __('Stock status & Qty')],
                ['value'=> self::QTY, 'label'=> __('Qty only')],
        ];
    }
}

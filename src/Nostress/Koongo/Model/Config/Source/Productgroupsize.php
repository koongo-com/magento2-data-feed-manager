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
* Config source for dropdown menu "Product group size"
*
* @category Nostress
* @package Nostress_Koongo
*
*/
namespace Nostress\Koongo\Model\Config\Source;

class Productgroupsize extends \Nostress\Koongo\Model\Config\Source
{
    public function toOptionArray()
    {
        return [
            ['value'=>'100', 'label'=> __('100')],
            ['value'=>'300', 'label'=> __('300')],
            ['value'=>'500', 'label'=> __('500')],
            ['value'=>'1000', 'label'=> __('1000')],
            ['value'=>'2000', 'label'=> __('2000')],
            ['value'=>'5000', 'label'=> __('5000')],
            ['value'=>'10000', 'label'=> __('10000')],
            ['value'=>'20000', 'label'=> __('20000')],
        ];
    }
}

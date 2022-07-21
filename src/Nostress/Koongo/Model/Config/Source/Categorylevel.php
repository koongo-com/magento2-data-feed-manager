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
* Config source for dropdown menu "Category Lowest Level"
*
* @category Nostress
* @package Nostress_Koongo
*
*/
namespace Nostress\Koongo\Model\Config\Source;

class Categorylevel extends \Nostress\Koongo\Model\Config\Source
{
    public function toOptionArray()
    {
        return [
            ['value'=>'1', 'label'=> '1'],
            ['value'=>'2', 'label'=> '2'],
            ['value'=>'3', 'label'=> '3'],
            ['value'=>'4', 'label'=> '4'],
            ['value'=>'5', 'label'=> '5'],
            ['value'=>'6', 'label'=> '6'],
            ['value'=>'7', 'label'=> '7'],
            ['value'=>'8', 'label'=> '8'],
            ['value'=>'9', 'label'=> '9'],
            ['value'=>'10', 'label'=> '10'],
        ];
    }
}

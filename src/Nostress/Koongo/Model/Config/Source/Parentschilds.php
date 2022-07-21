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
 * Config source model - parent child dependency
*
* @category Nostress
* @package Nostress_Koongo
*
*/
namespace Nostress\Koongo\Model\Config\Source;

class Parentschilds extends \Nostress\Koongo\Model\Config\Source
{
    const PARENTS_AND_CHILDS = 0;
    const PARENTS_ONLY = 1;
    const CHILDS_ONLY = 2;

    public function toOptionArray()
    {
        return [
                ['value'=> self::PARENTS_AND_CHILDS, 'label'=> __('Products and Variants')],
                ['value'=>self::PARENTS_ONLY, 'label'=> __('Products only (without Variants/Childs)')],
                ['value'=>self::CHILDS_ONLY, 'label'=> __('Variants only (without Parent Products)')],
        ];
    }
}

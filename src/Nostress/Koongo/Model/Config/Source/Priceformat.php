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
* Config source model - price format
*
* @category Nostress
* @package Nostress_Koongo
*
*/
namespace Nostress\Koongo\Model\Config\Source;

class Priceformat extends \Nostress\Koongo\Model\Config\Source
{
    const STANDARD = "standard";
    const CURRENCY_SUFFIX = "currency_suffix";
    const CURRENCY_PREFIX = "currency_prefix";
    const SYMBOL_SUFFIX = "symbol_suffix";
    const SYMBOL_PREFIX = "symbol_prefix";

    public function toOptionArray()
    {
        return [
            ['value'=> self::STANDARD, 'label'=> __('Standard e.g. 149.99')],
            ['value'=>self::CURRENCY_SUFFIX, 'label'=> __('Currency suffix e.g. 149.99 USD')],
            ['value'=>self::CURRENCY_PREFIX, 'label'=> __('Currency prefix e.g. USD 149.99')],
            ['value'=>self::SYMBOL_SUFFIX, 'label'=> __('Symbol suffix e.g 149.99 US$')],
            ['value'=>self::SYMBOL_PREFIX, 'label'=> __('Symbol prefix e.g US$ 149.99')]
        ];
    }
}

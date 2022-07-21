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
* Config source for attribute composition Macros
*
* @category Nostress
* @package Nostress_Koongo
*
*/
namespace Nostress\Koongo\Model\Config\Source;

class Macros extends \Nostress\Koongo\Model\Config\Source
{
    public function toOptionArray()
    {
        return [
                ['value'=> '{{name}} - {{sku}} - {{color}} - Large', 'label'=> __("Attribute Merge")],
            ['value'=> '[[round({{nkp_price_final_include_tax}} * 1.15, 2)]]', 'label'=> __("Increase Price by 15%")],
            ['value'=> '[[round({{nkp_price_final_include_tax}} + 25, 2)]]', 'label'=> __("Increase Price by 25")],
            ['value'=> '[[ +-*/ ]]', 'label'=> __("Empty Math Operation")],
            ['value'=> "[[('{{meta_title}}' != '')? '{{meta_title}}': '{{name}}';]]"
                    , 'label'=> __("Empty Attribute Condition")],
            ['value'=> "[[ ( {{nkp_price_final_include_tax}} > 100 ) ?  {{nkp_price_final_include_tax}}: {{nkp_price_final_include_tax}} + 20;]]"
                        , 'label'=> __("Attribute Value Condition")]

        ];
    }
}

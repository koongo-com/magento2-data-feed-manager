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
* Config source model - Column delimiter
*
* @category Nostress
* @package Nostress_Koongo
*
*/
namespace Nostress\Koongo\Model\Config\Source;

class Columndelimiter extends \Nostress\Koongo\Model\Config\Source
{
    public function toOptionArray()
    {
        $options = [];
        $values = $this->getOptions();
        foreach ($values as $index => $label) {
            $options[] = ['value' => $index, 'label' => $label];
        }
        return $options;
    }

    public function toIndexedArray()
    {
        return $this->getOptions();
    }

    protected function getOptions()
    {
        $options = ["|" => __("Pipe") . " - ( | )",
                            "," => __("Comma") . " - ( , )",
                            "\t" => __("Tab") . " - ( \\t )",
                            " " => __("Space") . " - ( ' ' )",
                            ";" => __("Semicolon") . " - ( ; )",
                            "/" => __("Slash") . " - ( / )",
                            "-" => __("Dash") . " - ( - )",
                            "*" => __("Star") . " - ( * )",
                            '\\' => __("Backslash") . " - ( \\ )",
                            ":" => __("Colon") . " - ( : )",
                            "#" => __("Grid") . " - ( # )",
                            "&" => __("Ampersand") . " - ( & )",
                            "~" => __("Tilde") . " - ( ~ )"
                            ];

        return $options;
    }
}

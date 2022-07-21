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
* Config source for dropdown menu "Include pub folder"
*
* @category Nostress
* @package Nostress_Koongo
*
*/
namespace Nostress\Koongo\Model\Config\Source;

class Imageattributesource extends \Nostress\Koongo\Model\Config\Source
{
    const STORE_VIEW_OR_DEFAULT = "store_view_or_default";
    const STORE_VIEW = "store_view";
    const DEFAULT_VALUES = "default_values";

    public function toOptionArray()
    {
        return [//Image data source (labels, enabled/disabled, etc. ): Store View + Default (recommended), Store view, Default value
            ['value'=> self::STORE_VIEW_OR_DEFAULT, 'label'=> __('Store view or Default values (Recommended)')],
            ['value'=> self::STORE_VIEW, 'label'=> __('Store view')],
            ['value'=> self::DEFAULT_VALUES, 'label'=> __('Default values')]
        ];
    }
}

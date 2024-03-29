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
 * Rewrite of original fieldset renderer Magento\Backend\Block\Widget\Form\Renderer\Fieldset
 *
 * @category Nostress
 * @package Nostress_Koongo
 *
 */

// @codingStandardsIgnoreFile

namespace Nostress\Koongo\Block\Widget\Form\Renderer;

/**
 * Form fieldset default renderer
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class FieldsetOriginal extends \Magento\Backend\Block\Widget\Form\Renderer\Fieldset
{
    /**
     * @var string
     */
    protected $_nameInLayout = '';
}

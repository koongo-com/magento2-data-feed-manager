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
* Config source for multiselect menu "Post Process Action"
*
* @category Nostress
* @package Nostress_Koongo
*
*/
namespace Nostress\Koongo\Model\Config\Source;

class Postprocactions extends \Nostress\Koongo\Model\Config\Source
{
    const PP_CDATA = 'cdata';
    const PP_ENCODE_SPECIAL = 'encode_special_chars';
    const PP_DECODE_SPECIAL = 'decode_special_chars';
    const PP_ENCODE_HTML_ENTITY = 'encode_html_entity';
    const PP_DECODE_HTML_ENTITY = 'decode_html_entity';
    const PP_REMOVE_EOL = 'remove_eol';
    const PP_STRIP_TAGS = 'strip_tags';
    const PP_DELETE_SPACES = 'delete_spaces';

    public function toOptionArray()
    {
        return [
            ['value'=>self::PP_CDATA, 'label'=> __("Encalpsulate into <![CDATA[]]>")],
            ['value'=>self::PP_ENCODE_HTML_ENTITY, 'label'=> __("Convert Text to HTML")],
            ['value'=>self::PP_ENCODE_SPECIAL, 'label'=> __("Convert Text to HTML - only: &, \", ', <, >")],
            ['value'=>self::PP_DECODE_HTML_ENTITY, 'label'=> __("Convert HTML to Text")],
            ['value'=>self::PP_DECODE_SPECIAL, 'label'=> __("Convert HTML to Text - only: &amp;, &quot;, &#039;, &lt, &gt;")],
            ['value'=>self::PP_REMOVE_EOL, 'label'=> __("Remove End of Line Characters")],
            ['value'=>self::PP_STRIP_TAGS, 'label'=> __("Remove HTML Tags/Elements")],
            ['value'=>self::PP_DELETE_SPACES, 'label'=> __("Remove Spaces")]
        ];
    }
}

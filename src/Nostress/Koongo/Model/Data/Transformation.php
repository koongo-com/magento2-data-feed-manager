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
* Data loader for export process
* @category Nostress
* @package Nostress_Koongo
*
*
*/

namespace Nostress\Koongo\Model\Data;

abstract class Transformation extends \Magento\Framework\Model\AbstractModel
{
    const BRACE_DOUBLE_OPEN = "{{";
    const BRACE_DOUBLE_CLOSE = "}}";
    const CDATA_PREFIX = "<![CDATA[";
    const CDATA_SUFFIX = "]]>";
    const GROUP_ROW_SEPARATOR = ";;";
    const GROUP_ROW_ITEM_SEPARATOR = "||";
    const PRICE_FORMAT_STANDARD = "standard";
    const PRICE_FORMAT_CURRENCY_SUFFIX = "currency_suffix";
    const PRICE_FORMAT_CURRENCY_PREFIX = "currency_prefix";
    const PRICE_FORMAT_SYMBOL_SUFFIX = "symbol_suffix";
    const PRICE_FORMAT_SYMBOL_PREFIX = "symbol_prefix";
    const PCH_PARENTS_AND_CHILDS = 0;
    const PCH_PARENTS_ONLY = 1;
    const PCH_CHILDS_ONLY = 2;

    const PLATFORM_ATTRIBUTE = "magento";
    const CONSTANT = 'constant';
    const COMPOSED_VALUE = 'composed_value';
    const CURRENCY = 'currency';
    const COUNTRY_CODE = 'country_code';
    const LOCALE = 'locale';
    const LANGUAGE = 'language';
    const COUNTRY = 'country';
    const FILE_URL = 'file_url';

    //shipping settings
    const SHIPPING_METHOD_NAME = "shipping_method_name";
    const SHIPPING_COST = "shipping_cost";
    const COST_SETUP = "cost_setup";
    const METHOD_NAME = "method_name";
    const DEPENDENT_ATTRIBUTE = "dependent_attribute";
    const PRICE_FROM = "price_from";
    const PRICE_TO = "price_to";
    const COST = "cost";
    const SHIPPING_INTERVAL_MAX = 1000000;
    const SHIPPING_INTERVAL_MIN = 0;

    const PARENT = 'parent';
    const CHILD = 'child';
    const PARENT_ATTRIBUTE_VALUE = 'eppav';
    const POST_PROCESS = 'postproc';
    const LABEL = 'label';
    const CODE = 'code';
    const LIMIT = 'limit';
    const TYPE = "type";
    const XML = 'xml';
    const CSV = 'csv';
    const TXT = 'txt';
    const HTML = 'html';
    const XSLT = 'xslt';
    const TIME = 'time';
    const DATE = 'date';
    const DATE_TIME = 'date_time';
    const COMPOSED_VALUE_VARS = 'composed_value_vars';
    const ORIGINAL_CODE = "original_code";
    const XML_ITEM_LABEL = "xml_item_label";
    const VALUE = "value";
    const POSTPROC_DELIMITER = ",";
    const PARAM = 'param';
    const TEXT_ENCLOSURE = 'text_enclosure';
    const COLUMN_DELIMITER = 'column_delimiter';
    const NEWLINE = 'new_line';
    const CUSTOM_COLUMNS_HEADER = "custom_columns_header";
    const COLUMNS_HEADER = "columns_header";
    const PRODUCT_TYPE = "product_type";
    const ATTRIBUTE = 'attribute';
    const TAG = 'tag';
    const LEVEL = 'level';
    const ID = 'id';
    const PATH_IDS = 'path_ids';
    const CHILDREN = 'children';
    const URL = 'url';

    /**
     * Prefix for product attributes defined by this module
     * @var unknown_type
     */
    const MODULE_PATTRIBUTE_PREFIX = 'nkp_';

    /**
     * Prefix for category attributes defined by this module
     * @var unknown_type
     */
    const MODULE_CATTRIBUTE_PREFIX = 'nkc_';

    protected $_dstData;

    public function init($params)
    {
        $this->setData($params);
        $this->_dstData = "";
    }

    public function getResult($allData = false)
    {
        return $this->_dstData;
    }

    protected function appendResult($string)
    {
        $this->_dstData .= $string;
    }

    public function transform($data)
    {
        $this->check($data);
    }

    protected function checkSrc($data)
    {
        if (!isset($data) || empty($data)) {
            return false;
        }
        return true;
    }

    protected function throwException($message)
    {
        throw new \Exception($message);
    }

    /**
     * Get Xslt Processor class for XSL transformation
     * @return New \XsltProcessor object.
     */
    protected function getXsltProcessor()
    {
        if (!class_exists("XsltProcessor")) {
            $this->throwException("1");
        }
        return new \XsltProcessor();
    }

    /**
     * Get Dom Document class for XSL transformation
     * @return New \DomDocument object.
     */
    protected function getDomDocument()
    {
        return new \DomDocument();
    }

    protected function getArrayField($index, $array, $default = null)
    {
        if (!is_array($array)) {
            return $default;
        }
        if (array_key_exists($index, $array)) {
            return $array[$index];
        } else {
            return $default;
        }
    }

    ////////// Helper functions //////////

    public function grebVariables($string, $replaceBraces = true, $asIndexedArray = false)
    {
        $pattern = "/" . self::BRACE_DOUBLE_OPEN . "[^}]*" . self::BRACE_DOUBLE_CLOSE . "/";
        $matches = [];
        $num = preg_match_all($pattern, $string, $matches);
        $matches = $matches[0];
        $result = [];
        if ($num) {
            foreach ($matches as $key => $data) {
                $withoutBraces = preg_replace("/[" . self::BRACE_DOUBLE_OPEN . "|" . self::BRACE_DOUBLE_CLOSE . "]/", "", $data);

                if ($asIndexedArray) {
                    $result[$data] = $withoutBraces;
                } else {
                    if ($replaceBraces) {
                        $result[$key] = $withoutBraces;
                    } else {
                        $result[$key] = $data;
                    }
                }
            }
        }
        return $result;
    }

    protected function removeCdataChars($string)
    {
        $string = str_replace(self::CDATA_PREFIX, "", $string);
        return str_replace(self::CDATA_SUFFIX, "", $string);
    }

    protected function getCdataString($input)
    {
        return self::CDATA_PREFIX . $input . self::CDATA_SUFFIX;
    }

    protected function changeEncoding($dstEnc, $input, $srcEnc=null)
    {
        if ($srcEnc == $dstEnc) {
            return $input;
        }

        if (!is_array($input)) {
            return $this->_changeEncoding($dstEnc, $input, $srcEnc);
        }

        $result = [];
        foreach ($input as $key => $item) {
            $result[$key] = $this->_changeEncoding($dstEnc, $item, $srcEnc);
        }
        return $result;
    }

    /*
     * Returns encoded string.
    */
    protected function _changeEncoding($dstEnc, $input, $srcEnc=null)
    {
        if (!isset($input) || empty($input)) {
            return $input;
        }

        $originalInput = $input;
        $extension = "mbstring";

        if (!isset($srcEnc)) {
            if (!extension_loaded($extension)) {
                throw new \Exception(__('PHP Extension "%1" must be loaded', $extension));
            } else {
                $srcEnc = mb_detect_encoding($input);
            }
        }
        try {
            $input = iconv($srcEnc, $dstEnc . '//TRANSLIT', $input);
        } catch (\Exception $e) {
            try {
                $input = iconv($srcEnc, $dstEnc . '//IGNORE', $input);
                //$input = mb_convert_encoding($input,$dstEnc,$srcEnc);
            } catch (\Exception $e) {
                //echo $input;
                throw $e;
            }
        }
        if ($input == false) {
            throw new \Exception('Conversion from encoding ' . $srcEnc . ' to ' . $dstEnc . ' failure. Following string can not be converted:<BR>' . $originalInput);
        }

        return $input;
    }

    protected function createCode($input, $delimiter = '_', $toLower = true, $skipChars = "")
    {
        $input = $this->removeDiacritic($input);
        if ($toLower) {
            $input = strtolower($input);
        }

        //replace characters which are not number or letters by space
        $input = preg_replace("/[^0-9a-zA-Z{$skipChars}]/", ' ', $input);
        $input = trim($input);
        //replace one or more spaces by delimiter
        $input = preg_replace('/\s+/', $delimiter, $input);

        return $input;
    }

    protected function removeDiacritic($input)
    {
        $transTable = [
                'Ă¤'=>'a',
                'Ă„'=>'A',
                'Ăˇ'=>'a',
                'Ă�'=>'A',
                'Ă '=>'a',
                'Ă€'=>'A',
                'ĂŁ'=>'a',
                'Ă�'=>'A',
                'Ă˘'=>'a',
                'Ă‚'=>'A',
                'ÄŤ'=>'c',
                'ÄŚ'=>'C',
                'Ä‡'=>'c',
                'Ä†'=>'C',
                'ÄŹ'=>'d',
                'ÄŽ'=>'D',
                'Ä›'=>'e',
                'Äš'=>'E',
                'Ă©'=>'e',
                'Ă‰'=>'E',
                'Ă«'=>'e',
                'Ă‹'=>'E',
                'Ă¨'=>'e',
                'Ă�'=>'E',
                'ĂŞ'=>'e',
                'ĂŠ'=>'E',
                'Ă­'=>'i',
                'ĂŤ'=>'I',
                'ĂŻ'=>'i',
                'ĂŹ'=>'I',
                'Ă¬'=>'i',
                'ĂŚ'=>'I',
                'Ă®'=>'i',
                'ĂŽ'=>'I',
                'Äľ'=>'l',
                'Ä˝'=>'L',
                'Äş'=>'l',
                'Äą'=>'L',
                'Ĺ„'=>'n',
                'Ĺ�'=>'N',
                'Ĺ�'=>'n',
                'Ĺ‡'=>'N',
                'Ă±'=>'n',
                'Ă‘'=>'N',
                'Ăł'=>'o',
                'Ă“'=>'O',
                'Ă¶'=>'o',
                'Ă–'=>'O',
                'Ă´'=>'o',
                'Ă”'=>'O',
                'Ă˛'=>'o',
                'Ă’'=>'O',
                'Ăµ'=>'o',
                'Ă•'=>'O',
                'Ĺ‘'=>'o',
                'Ĺ�'=>'O',
                'Ĺ™'=>'r',
                'Ĺ�'=>'R',
                'Ĺ•'=>'r',
                'Ĺ”'=>'R',
                'Ĺˇ'=>'s',
                'Ĺ '=>'S',
                'Ĺ›'=>'s',
                'Ĺš'=>'S',
                'ĹĄ'=>'t',
                'Ĺ¤'=>'T',
                'Ăş'=>'u',
                'Ăš'=>'U',
                'ĹŻ'=>'u',
                'Ĺ®'=>'U',
                'ĂĽ'=>'u',
                'Ăś'=>'U',
                'Ăą'=>'u',
                'Ă™'=>'U',
                'Ĺ©'=>'u',
                'Ĺ¨'=>'U',
                'Ă»'=>'u',
                'Ă›'=>'U',
                'Ă˝'=>'y',
                'Ăť'=>'Y',
                'Ĺľ'=>'z',
                'Ĺ˝'=>'Z',
                'Ĺş'=>'z',
                'Ĺą'=>'Z'
        ];
        return strtr($input, $transTable);
    }
}

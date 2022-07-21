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

class Encoding extends \Nostress\Koongo\Model\Config\Source
{
    const LABEL = 'label';
    const VALUE = 'value';
    const COUNTRY = 'country';

    protected $_options;

    public function toIndexedArray()
    {
        $options = $this->toOptionArray();
        $indexedArray = [];
        foreach ($options as $key => $item) {
            if (is_array($item)) {
                $prefix = $item[self::LABEL];

                foreach ($item[self::VALUE] as $subItem) {
                    $indexedArray[$subItem[self::VALUE]] = $prefix . " | " . $subItem[self::LABEL];
                }
            } else {
                $indexedArray[$key] = $item;
            }
        }
        return $indexedArray;
    }

    public function toOptionArray()
    {
        $options = ['utf-8' => 'utf-8'];
        $options[] = [
                'label' => 'ISO (Unix/Linux)', 'value' => [
                    [self::VALUE => 'iso-8859-1',self::LABEL => 'iso-8859-1'],
                    [self::VALUE => 'iso-8859-2' ,self::LABEL => 'iso-8859-2'],
                    [self::VALUE => 'iso-8859-3' ,self::LABEL => 'iso-8859-3'],
                    [self::VALUE => 'iso-8859-4' ,self::LABEL => 'iso-8859-4'],
                    [self::VALUE => 'iso-8859-5' ,self::LABEL => 'iso-8859-5'],
                    [self::VALUE => 'iso-8859-6' ,self::LABEL => 'iso-8859-6'],
                    [self::VALUE => 'iso-8859-7' ,self::LABEL => 'iso-8859-7'],
                    [self::VALUE => 'iso-8859-8' ,self::LABEL => 'iso-8859-8'],
                    [self::VALUE => 'iso-8859-9' ,self::LABEL => 'iso-8859-9'],
                    [self::VALUE => 'iso-8859-10' ,self::LABEL => 'iso-8859-10'],
                    [self::VALUE => 'iso-8859-11' ,self::LABEL => 'iso-8859-11'],
                    [self::VALUE => 'iso-8859-12' ,self::LABEL => 'iso-8859-12'],
                    [self::VALUE => 'iso-8859-13' ,self::LABEL => 'iso-8859-13'],
                    [self::VALUE => 'iso-8859-14' ,self::LABEL => 'iso-8859-14'],
                    [self::VALUE => 'iso-8859-15' ,self::LABEL => 'iso-8859-15'],
                    [self::VALUE => 'iso-8859-16' ,self::LABEL => 'iso-8859-16'],
                 ]];
        $options[] = [
                'label' => 'WINDOWS', 'value' => [
                    [self::VALUE => 'windows-1250' ,self::LABEL => 'windows-1250 - Central Europe'],
                    [self::VALUE => 'windows-1251' ,self::LABEL => 'windows-1251 - Cyrillic'],
                    [self::VALUE => 'windows-1252' ,self::LABEL => 'windows-1252 - Latin I'],
                    [self::VALUE => 'windows-1253' ,self::LABEL => 'windows-1253 - Greek'],
                    [self::VALUE => 'windows-1254' ,self::LABEL => 'windows-1254 - Turkish'],
                    [self::VALUE => 'windows-1255' ,self::LABEL => 'windows-1255 - Hebrew'],
                    [self::VALUE => 'windows-1256' ,self::LABEL => 'windows-1256 - Arabic'],
                    [self::VALUE => 'windows-1257' ,self::LABEL => 'windows-1257 - Baltic'],
                    [self::VALUE => 'windows-1258' ,self::LABEL => 'windows-1258 - Viet Nam'],
                ]];
        $options[] = [
                'label' => 'DOS', 'value' => [
                    [self::VALUE => 'cp437' ,self::LABEL => 'cp437 - Latin US'],
                    [self::VALUE => 'cp737' ,self::LABEL => 'cp737 - Greek'],
                    [self::VALUE => 'cp775' ,self::LABEL => 'cp775 - BaltRim'],
                    [self::VALUE => 'cp850' ,self::LABEL => 'cp850 - Latin1'],
                    [self::VALUE => 'cp852' ,self::LABEL => 'cp852 - Latin2'],
                    [self::VALUE => 'cp855' ,self::LABEL => 'cp855 - Cyrylic'],
                    [self::VALUE => 'cp857' ,self::LABEL => 'cp857 - Turkish'],
                    [self::VALUE => 'cp860' ,self::LABEL => 'cp860 - Portuguese'],
                    [self::VALUE => 'cp861' ,self::LABEL => 'cp861 - Iceland'],
                    [self::VALUE => 'cp862' ,self::LABEL => 'cp862 - Hebrew'],
                    [self::VALUE => 'cp863' ,self::LABEL => 'cp863 - Canada'],
                    [self::VALUE => 'cp864' ,self::LABEL => 'cp864 - Arabic'],
                    [self::VALUE => 'cp865' ,self::LABEL => 'cp865 - Nordic'],
                    [self::VALUE => 'cp866' ,self::LABEL => 'cp866 - Cyrylic Russian (used in IE "Cyrillic (DOS)" )'],
                    [self::VALUE => 'cp869' ,self::LABEL => 'cp869 - Greek2'],
                ]];
        $options[] = [
                'label' => 'MAC (Apple)', 'value' => [
                    [self::VALUE => 'x-mac-cyrillic' ,self::LABEL => 'x-mac-cyrillic'],
                    [self::VALUE => 'x-mac-greek' ,self::LABEL => 'x-mac-greek'],
                    [self::VALUE => 'x-mac-icelandic' ,self::LABEL => 'x-mac-icelandic'],
                    [self::VALUE => 'x-mac-ce' ,self::LABEL => 'x-mac-ce'],
                    [self::VALUE => 'x-mac-roman' ,self::LABEL => 'x-mac-roman'],
                ]];
        $options[] = [
                'label' => 'MISCELLANEOUS', 'value' => [
                    [self::VALUE => 'gsm0338' ,self::LABEL => 'gsm0338 (ETSI GSM 03.38)'],
                    [self::VALUE => 'cp037' ,self::LABEL => 'cp037'],
                    [self::VALUE => 'cp424' ,self::LABEL => 'cp424'],
                    [self::VALUE => 'cp500' ,self::LABEL => 'cp500'],
                    [self::VALUE => 'cp856' ,self::LABEL => 'cp856'],
                    [self::VALUE => 'cp875' ,self::LABEL => 'cp875'],
                    [self::VALUE => 'cp1006' ,self::LABEL => 'cp1006'],
                    [self::VALUE => 'cp1026' ,self::LABEL => 'cp1026'],
                    [self::VALUE => 'koi8-r' ,self::LABEL => 'koi8-r (Cyrillic)'],
                    [self::VALUE => 'koi8-u' ,self::LABEL => 'koi8-u (Cyrillic Ukrainian)'],
                    [self::VALUE =>  'nextstep' ,self::LABEL => 'nextstep'],
                    [self::VALUE =>  'us-ascii' ,self::LABEL => 'us-ascii'],
                    [self::VALUE => 'us-ascii-quotes' ,self::LABEL => 'us-ascii-quotes'],
                ]];

        return $options;
    }
}

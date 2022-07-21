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
* Class for taxonomy category management
*
* @category Nostress
* @package Nostress_Koongo
*
*/

namespace Nostress\Koongo\Model\Taxonomy\Category;

class Mapping extends \Nostress\Koongo\Model\AbstractModel
{
    const COL_TAXONOMY_CODE = 'taxonomy_code';
    const COL_LOCALE = 'locale';
    const COL_STORE_ID = 'store_id';
    const COL_CONFIG = "config";
    const CONFIG_RULES = 'rules';

    public function _construct()
    {
        $this->_init('Nostress\Koongo\Model\ResourceModel\Taxonomy\Category\Mapping');
    }

    public function getMapping($taxonomyCode, $locale, $storeId)
    {
        $collection = $this->getCollection();
        $collection->addFieldToFilter(self::COL_TAXONOMY_CODE, $taxonomyCode);
        $collection->addFieldToFilter(self::COL_LOCALE, $locale);
        $collection->addFieldToFilter(self::COL_STORE_ID, $storeId);
        $collection->getSelect();
        $collection->load();
        foreach ($collection as $item) {
            return $item;
        }
        return null;
    }

    public function getRules()
    {
        $config = $this->getConfigDecoded();
        $rules = [];
        if (isset($config[self::CONFIG_RULES])) {
            $rules = $config[self::CONFIG_RULES];
        }
        return $rules;
    }

    protected function getConfigDecoded()
    {
        return json_decode($this->getConfig(), true);
    }
}

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
 * ResourceModel for Koongo Connector feed layouts collection
 *
 * @category Nostress
 * @package Nostress_Koongo
 *
 */

namespace Nostress\Koongo\Model\ResourceModel\Channel\Feed;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    const COL_LINK = 'link';
    const COL_CODE = 'code';
    const COL_FILE_TYPE = 'file_type';
    const COL_ENABLED = 'enabled';
    const COUNTRY = 'country';

    const LABEL = 'label';

    const DEF_ENABLED = '1';

    const YES = '1';
    const NO = '0';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Nostress\Koongo\Model\Channel\Feed', 'Nostress\Koongo\Model\ResourceModel\Channel\Feed');
    }

    public function toOptionArray($enabled = null, $addFileType = null)
    {
        $additional = [self::COUNTRY => self::COUNTRY];
        if (isset($enabled)) {
            $additional[self::COL_ENABLED] = self::COL_ENABLED;
        }
        if (isset($addFileType)) {
            $additional[self::COL_FILE_TYPE] = self::COL_FILE_TYPE;
        }

        $options = $this->_toOptionArray(self::COL_CODE, self::COL_LINK, $additional);

        foreach ($options as $key => $option) {
            if (isset($option[self::COL_ENABLED]) && $option[self::COL_ENABLED] != self::DEF_ENABLED) {
                unset($options[$key]);
                continue;
            }
            if (isset($option[self::COL_FILE_TYPE]) && $addFileType == self::YES) {
                $options[$key][self::LABEL] .= " - " . $option[self::COL_FILE_TYPE];
            }
        }

        return $options;
    }

    public function addFieldsToFilter($fields)
    {
        if (!is_array($fields)) {
            return;
        }
        foreach ($fields as $field => $condition) {
            $this->addFieldToFilter($field, $condition);
        }
    }

    public function addFieldsToSelect($fields)
    {
        if (!is_array($fields)) {
            return;
        }

        foreach ($fields as $alias => $field) {
            $this->addFieldToSelect($field, $alias);
        }
    }
}

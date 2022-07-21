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
 * Channel profile edit actions
 *
 * @category Nostress
 * @package Nostress_Koongo
 *
 */
namespace Nostress\Koongo\Ui\Component\Listing\Column;

/**
 * Class PageActions
 */
class ProfileEditActions extends Actions\ColumnAbstract
{
    /** Url path */
    const KOONGO_PROFILE_URL_PATH_EDIT_GENERAL = 'koongo/channel_profile/editgeneral';
    const KOONGO_PROFILE_URL_PATH_EDIT_FILTER = 'koongo/channel_profile/editfilter';
    const KOONGO_PROFILE_URL_PATH_EDIT_CATEGORIES = 'koongo/channel_profile/editcategories';

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');

                if (isset($item['entity_id'])) {
                    $item[$name]['edit_feed'] = [
                        'href' => $this->urlBuilder->getUrl(self::KOONGO_PROFILE_URL_PATH_EDIT_GENERAL, ['entity_id' => $item['entity_id']]),
                        'label' => __('Attributes')
                    ];
                    $item[$name]['edit_filter'] = [
                        'href' => $this->urlBuilder->getUrl(self::KOONGO_PROFILE_URL_PATH_EDIT_FILTER, ['entity_id' => $item['entity_id']]),
                        'label' => __('Filter')
                    ];

                    //Get channel label
                    $this->channel->setChannelCode($item['channel_code']);
                    $categoriesLinkLabel = $this->channel->getLabel() . " " . __('Categories');
                    if (empty($item['taxonomy_code'])) {
                        $categoriesLinkLabel .= " (N/A)";
                    }

                    $item[$name]['edit_taxonomy'] = [
                        'href' => $this->urlBuilder->getUrl(self::KOONGO_PROFILE_URL_PATH_EDIT_CATEGORIES, ['entity_id' => $item['entity_id']]),
                        'label' => $categoriesLinkLabel
                    ];
                }
            }
        }

        return parent::prepareDataSource($dataSource);
    }
}

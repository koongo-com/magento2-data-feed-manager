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
class FeedFileLinkActions extends Actions\ColumnAbstract
{

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
                $profile = new \Magento\Framework\DataObject($item);

                $this->channel->setChannelCode($profile->getChannelCode());

                $name = $this->getData('name');

                $item[ $name]['title'] = __('Feed Preview for Profile #%1', $profile->getEntityId());

                $item[ $name]['manual_url'] = $this->channel->getManualUrl();
                $item[ $name]['download_url'] = $this->urlBuilder->getUrl('koongo/channel_profile/download', ['entity_id' => $item['entity_id']]);
                $item[ $name]['preview_url'] = $this->urlBuilder->getUrl('koongo/channel_profile/preview', ['entity_id' => $item['entity_id']]);
                $item[ $name]['edit_general'] = $this->urlBuilder->getUrl('koongo/channel_profile/editgeneral', ['entity_id' => $item['entity_id']]);

                $item[ $name]['edit_url'] = "location.href = '" . $this->urlBuilder->getUrl('koongo/channel_profile_ftp/edit', ['entity_id' => $item['entity_id']]) . "';";
                $config = json_decode($item['config'], true);
                if ($this->ftp->isFilled($config)) {
                    $item[ $name]['upload_enabled'] = true;
                    $item[ $name]['upload_url'] = $this->urlBuilder->getUrl('koongo/channel_profile_ftp/upload', ['entity_id' => $item['entity_id']]);
                } else {
                    $item[ $name]['upload_enabled'] = false;
                }

                $item[ $name]['channel'] = $item['link'];
                $item[ $name]['feed_url'] = $item['url'];
                $item[ $name]['feed_file_type'] = ucfirst($item['file_type']);

                $item[ $name]['preview_help_url'] = $this->ftp->helper->getHelp('feed_preview');
            }
        }

        return parent::prepareDataSource($dataSource);
    }
}

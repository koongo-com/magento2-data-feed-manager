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
class ProfileRunActions extends Actions\ColumnAbstract
{
    /** Url path */
    const KOONGO_PROFILE_URL_PATH_EXECUTE = 'koongo/channel_profile/execute';
    const KOONGO_PROFILE_URL_PATH_SCHEDULE = 'koongo/channel_profile/editcron';
    const KOONGO_PROFILE_URL_PATH_EDIT_FTP = 'koongo/channel_profile_ftp/edit';

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
                    $item[$name]['run_profile'] = [
                        'href' => $this->urlBuilder->getUrl(self::KOONGO_PROFILE_URL_PATH_EXECUTE, ['entity_id' => $item['entity_id']]),
                        'label' => __('Execute')
                    ];
                    $item[$name]['schedule_profile'] = [
                        'href' => $this->urlBuilder->getUrl(self::KOONGO_PROFILE_URL_PATH_SCHEDULE, ['entity_id' => $item['entity_id']]),
                        'label' => __('Schedule')
                    ];

                    $item[$name]['edit_ftp'] = [
                        'href' => $this->urlBuilder->getUrl(self::KOONGO_PROFILE_URL_PATH_EDIT_FTP, ['entity_id' => $item['entity_id']]),
                        'label' => __('FTP Submission')
                    ];
                }
            }
        }

        return parent::prepareDataSource($dataSource);
    }

    public static function getFtpConfig($urlBuilder, $item)
    {
        return [
                'edit_url' => $urlBuilder->getUrl(self::KOONGO_PROFILE_URL_PATH_EDIT_FTP, ['entity_id' => $item['entity_id']]),
                'title' => __('Ftp Submission for Profile #%1', $item['entity_id']),
                'label' => __('Ftp Submission')
        ];
    }
}

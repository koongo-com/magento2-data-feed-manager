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
 * Channel column renderer
 *
 * @category Nostress
 * @package Nostress_Koongo
 *
 */

namespace  Nostress\Koongo\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Nostress\Koongo\Model\Translation;
use Nostress\Koongo\Ui\Component\Listing\Column\Status\Options;

class Message extends \Magento\Ui\Component\Listing\Columns\Column
{
    const MAX_CELL_LABEL_LEN = 40;

    protected Options $statusOptions;
    protected Translation $translation;
    protected UrlInterface $urlBuilder;

    /**
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        Options $statusOptions,
        Translation $translation,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->urlBuilder = $urlBuilder;
        $this->statusOptions = $statusOptions;
        $this->translation = $translation;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                $profile = new \Magento\Framework\DataObject($item);
                $message = $this->translation->replaceActionLinks($profile->getMessage());

                //Status is encalsulated at span and is upper case
                $status = ucfirst(strtolower(strip_tags($profile->getStatus())));

                $cellLabel = $message;
                if ($status == __('Error')) {
                    $cellLabel = trim(substr($cellLabel, 0, self::MAX_CELL_LABEL_LEN)) . "... <a href='#' title='" . __("Click to view full message") . "'>" . __("Read More") . "</a>";
                }

                $item[$fieldName . '_html'] = $cellLabel;
                $item[$fieldName . '_message'] = $message;
                $item[$fieldName . '_status'] = $status;
                $item[$fieldName . '_title'] = __('Profile #%1 - Status & Message', $profile->getEntityId());
            }
        }

        return $dataSource;
    }
}

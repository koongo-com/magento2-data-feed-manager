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

namespace Nostress\Koongo\Ui\Component\Listing\Column;

use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

/**
 * Class Date
 */
class Date extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param TimezoneInterface $timezone
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        TimezoneInterface $timezone,
        array $components = [],
        array $data = []
    ) {
        $this->timezone = $timezone;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        //Don't transform date to current time scope
//         if (isset($dataSource['data']['items'])) {
//             foreach ($dataSource['data']['items'] as & $item) {
//                if (isset($item[$this->getData('name')])) {
//                     $date = $this->timezone->date(new \DateTime($item[$this->getData('name')]));
//                     if (isset($this->getConfiguration()['timezone']) && !$this->getConfiguration()['timezone']) {
//                         $date = new \DateTime($item[$this->getData('name')]);
//                     }
//                     $item[$this->getData('name')] = $date->format('Y-m-d H:i:s');
//                }
//             }
//         }

        return $dataSource;
    }
}

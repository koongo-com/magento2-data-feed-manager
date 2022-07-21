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
namespace Nostress\Koongo\Ui\Component\Listing\Column\Actions;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class PageActions
 */
class ColumnAbstract extends Column
{
    /** @var \Nostress\Koongo\Model\Channel */
    protected $channel;

    /**
     * @var \Nostress\Koongo\Model\Channel\Profile\Ftp
     */
    protected $ftp;

    /** @var UrlInterface */
    protected $urlBuilder;

    /**
     * @var \Nostress\Koongo\Helper\Version
     */
    protected $_helper;

    /**
     * Request instance
     *
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlBuilder $actionUrlBuilder
     * @param UrlInterface $urlBuilder
     * @param RequestInterface $request
     * @param array $components
     * @param array $data
     * @param string $editUrl
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        \Nostress\Koongo\Model\Channel $channel,
        \Nostress\Koongo\Helper\Version $helper,
        \Nostress\Koongo\Model\Channel\Profile\Ftp $ftp,
        RequestInterface $request,
        array $components = [],
        array $data = []
    ) {
        $this->channel = $channel;
        $this->ftp = $ftp;
        $this->urlBuilder = $urlBuilder;
        $this->_helper = $helper;
        $this->request = $request;

        $data['is_license_valid'] = $this->_helper->isLicenseValid();

        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (!isset($item[ 'is_license_valid'])) {
                    $item[ 'is_license_valid'] = $this->getData('is_license_valid');
                }
            }
        }

        return parent::prepareDataSource($dataSource);
    }
}

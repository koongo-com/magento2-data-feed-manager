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
 * Channel profile wizard - Feed type seletcion step
 *
 * @category Nostress
 * @package Nostress_Koongo
 *
 */

namespace Nostress\Koongo\Block\Adminhtml\Channel\Profile\Create\Steps;

use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\System\Store;
use Nostress\Koongo\Model\Channel\Feed as FeedModel;

class Feed extends \Magento\Ui\Block\Component\StepsWizard\StepAbstract
{
    /**
     * \Nostress\Koongo\Model\Channel\Feed
     */
    protected $_feedSource;
    private Store $_systemStore;

    public function __construct(
        Context   $context,
        Store $systemStore,
        FeedModel $feedSource
    ) {
        parent::__construct($context);
        $this->_systemStore = $systemStore;
        $this->_feedSource = $feedSource;
    }

    /**
     * {@inheritdoc}
     */
    public function getCaption()
    {
        return __('Feed & File Type');
    }

    public function getFeedsByLink($jsonEncode = true)
    {
        $filter = [FeedModel::COL_ENABLED => "1"];
        $collection = $this->_feedSource->getFeedCollection($filter, null, FeedModel::COL_TYPE);

        $feedsByLink = [];
        foreach ($collection as $item) {
            $link = $item->getLink();

            if (!isset($feedsByLink[$link])) {
                $feedsByLink[$link] = [];
            }
            $feedsByLink[$link][] = [
                "label" => $item->getType() . " (" . $item->getFileType() . ")",
                "code" => $item->getCode(),
                "id" => "feed-code-radio_" . $item->getId()
            ];
        }

        if ($jsonEncode) {
            $feedsByLink = json_encode($feedsByLink);
        }

        return $feedsByLink;
    }

    public function getChannelManualList()
    {
        $map = [];
        $collection = $this->_feedSource->getFeeds();
        foreach ($collection as $feed) {
            $map[ $feed->getCode()] = $feed->getChannel()->getManualUrl();
        }

        return $map;
    }
}

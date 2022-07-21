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
 * Export profiles grid controller
 *
 * @category Nostress
 * @package Nostress_Koongo
 *
 */

namespace Nostress\Koongo\Controller\Adminhtml\Channel;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

abstract class Profile extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     *
     * @var \Nostress\Koongo\Helper\Version
     */
    protected $version;

    /**
     * @var \Nostress\Koongo\Model\Channel\Profile\Manager
     */
    protected $manager;

    /**
     * @var \Nostress\Koongo\Model\Channel\ProfileFactory
     */
    protected $profileFactory;

    /**
     *
     * @var \Nostress\Koongo\Model\Translation
     */
    protected $translation;

    protected $_auth_label = 'Nostress_Koongo::koongo_channel_profile';

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Nostress\Koongo\Helper\Version $helper,
        \Nostress\Koongo\Model\Channel\Profile\Manager $manager,
        \Nostress\Koongo\Model\Channel\ProfileFactory $profileFactory,
        \Nostress\Koongo\Model\Translation $translation
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->version = $helper;
        $this->manager = $manager;
        $this->profileFactory = $profileFactory;
        $this->translation = $translation;

        parent::__construct($context);
    }

    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed($this->_auth_label);
    }

    protected function _sendAjaxError($message)
    {
        $this->getResponse()->representJson(
            $this->_objectManager->get(
                'Magento\Framework\Json\Helper\Data'
            )->jsonEncode(['error' => true, 'message' => $message])
        );
    }

    protected function _sendAjaxSuccess($message)
    {
        $this->getResponse()->representJson(
            $this->_objectManager->get(
                'Magento\Framework\Json\Helper\Data'
            )->jsonEncode($message)
        );
    }

    protected function _sendAjaxResponse($message, $error = false)
    {
        if (is_array($message) && isset($message['error'])) {
            $error = $message['error'];
            if ($error) {
                $message = $message['message'];
            }
        }

        if ($error) {
            $this->_sendAjaxError($message);
        } else {
            $this->_sendAjaxSuccess($message);
        }
    }

    protected function _isAjax()
    {
        return $this->getRequest()->getParam('isAjax');
    }

    /**
     * Provides form data from the serialized data.
     *
     * @param string $serializedData
     * @return array
     * @throws \InvalidArgumentException
     */
    protected function _unserializeFormData(string $serializedData)
    {
        $encodedFields = $this->_unserializeJson($serializedData);

        if (!is_array($encodedFields)) {
            throw new \InvalidArgumentException('Unable to unserialize value.');
        }

        $formData = [];
        foreach ($encodedFields as $item) {
            $decodedFieldData = [];
            parse_str($item, $decodedFieldData);
            $formData = array_replace_recursive($formData, $decodedFieldData);
        }

        return $formData;
    }

    /**
     * {@inheritDoc}
     * @since 101.0.0
     */
    protected function _unserializeJson($string)
    {
        $result = json_decode($string, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Unable to unserialize value.');
        }
        return $result;
    }
}

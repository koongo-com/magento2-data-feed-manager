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
 * Channel profile feed settings edit form category tab
 *
 * @category Nostress
 * @package Nostress_Koongo
 *
 */

namespace Nostress\Koongo\Block\Adminhtml\Channel\Profile\Ftp\Edit;

use Nostress\Koongo\Model\Channel\Profile;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /*
     * @var \Nostress\Koongo\Model\Config\Source\Ftpprotocol
     */
    protected $protocolSource;

    /*
     * @var \Nostress\Koongo\Model\Config\Source\Ftpencryption
     */
    protected $encryptionSource;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Nostress\Koongo\Model\Config\Source\Ftpprotocol $protocolSource,
        \Nostress\Koongo\Model\Config\Source\Ftpencryption $encryptionSource,
        array $data = []
    ) {
        $this->protocolSource = $protocolSource;
        $this->encryptionSource = $encryptionSource;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        /* @var $model \Nostress\Koongo\Model\Channel\Profile */
        $model = $this->_coreRegistry->registry('koongo_channel_profile');
        $config = $model->getConfigItem(Profile::CONFIG_FEED, false, Profile::CONFIG_FTP);

        $result = [];
        if (!empty($config)) {
            $result = $config;
        } else {
            $submissionParams = $model->getFeed()->getSubmissionDefaults(Profile::CONFIG_FTP);
            if (!empty($submissionParams)) {
                $result = $submissionParams;
            }
        }
        $config = $result;

        /*
         * Checking if user have permissions to save information
         */
        if ($this->_isAllowedAction('Nostress_Koongo::save')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
        }

        $action = $this->getUrl('*/channel_profile/save', [ 'entity_id'=>$model['entity_id']]);

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $action, 'method' => 'post']]
        );

        $fieldset = $form->addFieldset('ftp_fieldset', []);

        $yesnoSource = ['1' => __('Yes'), '0' => __('No')];

        $fieldset->addField(
            'back',
            'hidden',
            [
                    'name' => 'back',
                    'value' => ''
                ]
        );

        $fieldset->addField(
            'protocol',
            'select',
            [
                'name' => Profile::CONFIG_FEED . '[' . Profile::CONFIG_FTP . ']' . '[protocol]',
                'label' => __('Protocol:'),
                'required' => false,
                'disabled' => $isElementDisabled,
                'options' => $this->protocolSource->toIndexedArray(),
            ]
        );

        $fieldset->addField('hostname', 'text', [
            'label' => __("Host Name:"),
            'name' => Profile::CONFIG_FEED . '[' . Profile::CONFIG_FTP . ']' . '[hostname]',
            'note' => 'e.g. "ftp.domain.com"',
            'disabled' => $isElementDisabled,
        ]);

        $fieldset->addField('port', 'text', [
            'label' => __("Port:"),
            'name' => Profile::CONFIG_FEED . '[' . Profile::CONFIG_FTP . ']' . '[port]',
            'value' => 21,
            'disabled' => $isElementDisabled,
        ]);

        $fieldset->addField('username', 'text', [
                'label' => __("User Name:"),
                'name' => Profile::CONFIG_FEED . '[' . Profile::CONFIG_FTP . ']' . '[username]',
                'disabled' => $isElementDisabled,
        ]);

        $fieldset->addField('password', 'password', [
                'label' => __("Password:"),
                'name' => Profile::CONFIG_FEED . '[' . Profile::CONFIG_FTP . ']' . '[password]',
                'disabled' => $isElementDisabled,
        ]);

        $fieldset->addField('path', 'text', [
                'label' => __("Path:"),
                'name' => Profile::CONFIG_FEED . '[' . Profile::CONFIG_FTP . ']' . '[path]',
                'value' => '/',
                'note' => 'e.g. "/yourfolder"',
                'disabled' => $isElementDisabled,
        ]);

        $fieldset->addField('passive_mode', 'select', [
                'label' => __("Passive Mode:"),
                'name' => Profile::CONFIG_FEED . '[' . Profile::CONFIG_FTP . ']' . '[passive_mode]',
                'values' => $yesnoSource,
                'disabled' => $isElementDisabled,
        ]);

        $fieldset->addField(
            'ssl',
            'select',
            [
                'name' => Profile::CONFIG_FEED . '[' . Profile::CONFIG_FTP . ']' . '[ssl]',
                'label' => __('Encryption:'),
                'required' => false,
                'disabled' => $isElementDisabled,
                'options' => $this->encryptionSource->toIndexedArray(),
            ]
        );

        $fieldset->addField(
            'enabled',
            'select',
            [
                        'name' => Profile::CONFIG_FEED . '[' . Profile::CONFIG_FTP . ']' . '[enabled]',
                        'label' => __('Auto-Submit'),
                        'title' => __('Auto-Submit'),
                        'required' => false,
                        'disabled' => $isElementDisabled,
                        'options' => $yesnoSource,
                        'note' => __('If Enabled, feed will be submited automaticly after scheduled execution.')
                ]
        );

        $fieldset->addField('test_connection', 'button', [
            'value' => __("Test Connection"),
            'name' => Profile::CONFIG_FEED . '[' . Profile::CONFIG_FTP . ']' . '[test_connection]',
            'class' => 'action-primary abs-action-l',
            'label' => ' ' // for aligning button to other inputs
        ]);

        $form->addValues($config);
        $form->setUseContainer(true);

        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}

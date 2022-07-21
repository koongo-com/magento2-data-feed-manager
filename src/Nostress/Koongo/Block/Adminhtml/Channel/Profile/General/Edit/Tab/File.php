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
 * Channel profile feed settings edit form feed file tab
 *
 * @category Nostress
 * @package Nostress_Koongo
 *
 */

namespace Nostress\Koongo\Block\Adminhtml\Channel\Profile\General\Edit\Tab;

use Nostress\Koongo\Model\Channel\Profile;

class File extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /*
     * @var \Nostress\Koongo\Model\Config\Source\Encoding
    */
    protected $encodingSource;

    /*
     * @var \Nostress\Koongo\Model\Config\Source\Textenclosure
     */
    protected $textenclosureSource;

    /*
     * @var \Nostress\Koongo\Model\Config\Source\Columndelimiter
    */
    protected $columndelimiterSource;

    /*
     * @var \Nostress\Koongo\Model\Config\Source\Newlinedelimiter
    */
    protected $newlinedelimiterSource;

    /**
     *
     * @var \Nostress\Koongo\Helper
     */
    protected $helper;

    /**
     * @var \Nostress\Koongo\Block\Widget\Form\Renderer\Fieldset
     */
    protected $_rendererFieldset;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     * @param \Nostress\Koongo\Model\Config\Source\Encoding $encodingSource
     * @param\Nostress\Koongo\Model\Config\Source\Textenclosure $textenclosureSource
     * @param \Nostress\Koongo\Model\Config\Source\Columndelimiter $columndelimiterSource
     * @param \Nostress\Koongo\Model\Config\Source\Newlinedelimiter $newlinedelimiterSource
     * @param \Nostress\Koongo\Helper\Data $helper
     * @param \Nostress\Koongo\Block\Widget\Form\Renderer\Fieldset $rendererFieldset
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Nostress\Koongo\Model\Config\Source\Encoding $encodingSource,
        \Nostress\Koongo\Model\Config\Source\Textenclosure $textenclosureSource,
        \Nostress\Koongo\Model\Config\Source\Columndelimiter $columndelimiterSource,
        \Nostress\Koongo\Model\Config\Source\Newlinedelimiter $newlinedelimiterSource,
        \Nostress\Koongo\Helper\Data $helper,
        \Nostress\Koongo\Block\Widget\Form\Renderer\Fieldset $rendererFieldset,
        array $data = []
    ) {
        $this->encodingSource = $encodingSource;
        $this->textenclosureSource = $textenclosureSource;
        $this->columndelimiterSource = $columndelimiterSource;
        $this->newlinedelimiterSource = $newlinedelimiterSource;
        $this->_rendererFieldset = $rendererFieldset;
        $this->helper = $helper;
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
        $config = $model->getConfigItem(Profile::CONFIG_FEED, true, Profile::CONFIG_COMMON);
        $channelLabel = $model->getChannel()->getLabel();

        /*
         * Checking if user have permissions to save information
         */
        if ($this->_isAllowedAction('Nostress_Koongo::save')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
        }

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('channel_profile_');

        $fieldset = $form->addFieldset('file_fieldset', [
            'legend' => __('Feed File') . $model->helper->renderTooltip('advanced_feed_file')
        ])->setRenderer($this->_rendererFieldset);

        $fieldset->addField(
            'filename',
            'text',
            [
                'name' => 'filename',
                'label' => __('Feed File Name'),
                'title' => __('Feed File Name'),
                'required' => true,
                'disabled' => $isElementDisabled,
                'note' => __('File Name without suffix - .xml, .csv or .txt will be added according to selected feed type.')
            ]
        );

        $fieldset->addField(
            'compress_file',
            'select',
            [
                'name' => Profile::CONFIG_GENERAL . '[compress_file]',
                'label' => __('Compress File'),
                'title' => __('Compress File'),
                'required' => false,
                'options' => ['1' => __('Yes'), '0' => __('No')],
                'note' => __("You can compress feed file to ZIP archive.")
            ]
        );

        $fieldset->addField(
            'encoding',
            'select',
            [
                'name' => Profile::CONFIG_FEED . '[' . Profile::CONFIG_COMMON . ']' . '[encoding]',
                'label' => __('Encoding'),
                'title' => __('Encoding'),
                'required' => false,
                'options' => $this->encodingSource->toIndexedArray(),
                'disabled' => $isElementDisabled,
                'note' => __('Encoding of the feed file. In most cases use default value UTF-8.')
            ]
        );

        $fileType = $model->getFeed()->getFileType();

        if ($fileType != "xml") {
            $fieldsetCsv = $form->addFieldset('filecsv_fieldset', ['legend' => __('CSV/TXT File Format')]);

            $fieldsetCsv->addField(
                'text_enclosure',
                'select',
                [
                    'name' => Profile::CONFIG_FEED . '[' . Profile::CONFIG_COMMON . ']' . '[text_enclosure]',
                    'label' => __('Text Enclosure'),
                    'title' => __('Text Enclosure'),
                    'required' => false,
                    'options' => $this->textenclosureSource->toIndexedArray(),
                    'disabled' => $isElementDisabled,
                    'note' => __("Character is utilized to enclose particular attribute value within it's column.")
                   ]
            );

            $fieldsetCsv->addField(
                'column_delimiter',
                'select',
                [
                    'name' => Profile::CONFIG_FEED . '[' . Profile::CONFIG_COMMON . ']' . '[column_delimiter]',
                    'label' => __('Column Delimiter'),
                    'title' => __('Column Delimiter'),
                    'required' => false,
                    'options' => $this->columndelimiterSource->toIndexedArray(),
                    'disabled' => $isElementDisabled,
                    'note' => __("Delimiter, which separates particular columns within exported file.")
                ]
            );

            $fieldsetCsv->addField(
                'new_line',
                'select',
                [
                    'name' => Profile::CONFIG_FEED . '[' . Profile::CONFIG_COMMON . ']' . '[new_line]',
                    'label' => __('New Line Character'),
                    'title' => __('New Line Character'),
                    'required' => false,
                    'options' => $this->newlinedelimiterSource->toIndexedArray(),
                    'disabled' => $isElementDisabled,
                    'note' => __("Character used for separtion of particular rows in exported file. ")
                ]
            );
        }

        $this->_eventManager->dispatch('adminhtml_koongo_channel_profile_general_edit_tab_file_prepare_form', ['form' => $form]);

        $data = $config;
        $data['filename'] = $model->getFilename(false, false, "");
        $data['compress_file'] = $model->getConfigItem(Profile::CONFIG_GENERAL, true, 'compress_file');

        $fields = ['encoding','text_enclosure','column_delimiter'];

        foreach ($fields as $field) {
            if (isset($config[$field])) {
                $data[$field] = $config[$field];
            }
        }

        if (isset($config['new_line'])) {
            $data['new_line'] = $this->helper->encodeSpaceCharacters($config['new_line']);
        }

        $form->setValues($data);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Feed File');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Feed File');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
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

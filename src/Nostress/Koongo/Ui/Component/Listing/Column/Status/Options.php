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
 * Status options
 *
 * @category Nostress
 * @package Nostress_Koongo
 *
 */

namespace Nostress\Koongo\Ui\Component\Listing\Column\Status;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Status Options for Cms Pages and Blocks
 */
class Options implements OptionSourceInterface
{
    /**
     * @var array
     */
    protected $options;

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options !== null) {
            return $this->options;
        }

        $this->options = [];
        $this->options[] = ['value'=> 0, 'label' => __('New')];
        $this->options[] = ['value'=> 1, 'label' => __('Running')];
        $this->options[] = ['value'=> 2, 'label' => __('Interrupted')];
        $this->options[] = ['value'=> 3, 'label' => __('Error')];
        $this->options[] = ['value'=> 4, 'label' => __('Finished')];
        $this->options[] = ['value'=> 5, 'label' => __('Enabled')];
        $this->options[] = ['value'=> 6, 'label' => __('Disabled')];
        return $this->options;
    }

    public function getCssClassMap()
    {
        $map = [];

        //Red
        $map[3] = 'critical';
        $map[2] = 'critical';

        //Green
        $map[4] = 'notice';

        //Orange
        $map[0] = 'minor';
        $map[1] = 'minor';
        $map[5] = 'minor';
        $map[6] = 'minor';

        return $map;
    }

    public function getDefaultCssClass()
    {
        return 'minor';
    }

    public function toIndexedArray()
    {
        $options = $this->toOptionArray();
        $indexedArray = [];
        foreach ($options as $option) {
            $indexedArray[$option['value']] = $option['label'];
        }

        return $indexedArray;
    }
}

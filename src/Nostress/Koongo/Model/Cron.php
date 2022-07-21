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
* Class for export profile cron execution
*
* @category Nostress
* @package Nostress_Koongo
*
*/

namespace Nostress\Koongo\Model;

use Nostress\Koongo\Model\Config\Source\Scheduledays;
use Nostress\Koongo\Model\Config\Source\Scheduletimes;

class Cron extends \Nostress\Koongo\Model\AbstractModel
{
    const COL_PROFILE_ID = 'profile_id';
    const COL_DAY_OF_WEEK = 'day_of_week';
    const COL_TIME = 'time';

    const HOUR_SECONDS = 3600;
    const MINUTE_SECONDS = 60;

    const TIME_MAX = "23:59:59";
    const TIME_MIN = "00:00:00";

    const TIME_FROM = 'time_from';
    const TIME_TO = 'time_to';
    const DAY_OF_WEEK = 'dow';

    public function _construct()
    {
        $this->_init('Nostress\Koongo\Model\ResourceModel\Cron');
    }

    /**
     * @var \Nostress\Koongo\Model\Config\Source\Datetimeformat
     */
    protected $_datetimeFormat;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Nostress\Koongo\Helper\Data $helper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Nostress\Koongo\Model\Translation $translation
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Nostress\Koongo\Helper\Data $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Nostress\Koongo\Model\Translation $translation,
        \Nostress\Koongo\Model\Config\Source\Datetimeformat $datetimeFormat,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_datetimeFormat = $datetimeFormat;
        parent::__construct($context, $registry, $helper, $storeManager, $translation, $resource, $resourceCollection, $data);
    }

    /**
    * Apply cron rules and adjust schedule records for export profile execution.
     */
    public function applyRules($profileId, $rules)
    {
        if (!isset($rules) || !is_array($rules)) {
            $rules = [];
        }

        //prepare cron table items
        $cronItems = [];
        foreach ($rules as $rule) {
            $newItems = $this->processRule($rule);
            foreach ($newItems as $index => $item) {
                $cronItems[$index] = $item;
            }
        }

        $existingCronItemIndexes = [];
        $collection = $this->getCollection()->addFieldToFilter("profile_id", $profileId)->load();
        //Remove cron items from table
        foreach ($collection as $item) {
            $index = $item->getDayOfWeek() . $item->getTime();
            if (!isset($cronItems[$index])) {
                $item->delete();
            } else {
                $existingCronItemIndexes[] = $index;
            }
        }

        //Add new items
        foreach ($existingCronItemIndexes as $existingIndex) {
            unset($cronItems[$existingIndex]);
        }

        foreach ($cronItems as $newItem) {
            $this->setData($newItem);
            $this->setProfileId($profileId);
            $this->save();
        }
    }

    public function getScheduledProfileIds()
    {
        $intervals = $this->getScheduledIntervals();

        $collection = $this->getCollection();
        $select = $collection->getSelect();

        $whereSql = "";
        foreach ($intervals as $interval) {
            if (!empty($whereSql)) {
                $whereSql .= "OR ";
            }
            $whereSql .= "(time >= '{$interval[self::TIME_FROM]}' AND time <= '{$interval[self::TIME_TO]}' AND day_of_week = '{$interval[self::DAY_OF_WEEK]}') ";
        }
        $select->where($whereSql);
        $select->group("profile_id");

        //		echo $select->__toString();
        //		exit();

        $collection->load();
        $profileIds = [];
        foreach ($collection as $record) {
            $profileIds[] = $record->getProfileId();
        }

        return $profileIds;
    }

    protected function processRule($rule)
    {
        $cronItems = [];

        if (!isset($rule['enabled']) || $rule['enabled'] != "on") {
            return $cronItems;
        }

        //Prepare times
        $runTimes = [];
        if ($rule['times_interval'] == Scheduletimes::EVERY_24H) {
            $runTimes[] =  $this->calculateTimeInseconds($rule['time_hours'], $rule['time_minutes']);
        } else {
            $initialTime = 0;
            $maximumTime = $this->calculateTimeInseconds(24, 0);

            switch ($rule['times_interval']) {
                case Scheduletimes::EVERY_5M:
                    $interval = $this->calculateTimeInseconds(0, 5);
                    break;
                case Scheduletimes::EVERY_15M:
                    $interval = $this->calculateTimeInseconds(0, 15);
                    break;
                case Scheduletimes::EVERY_30M:
                    $interval = $this->calculateTimeInseconds(0, 30);
                    break;
                case Scheduletimes::EVERY_60M:
                    $interval = $this->calculateTimeInseconds(1, 0);
                    break;
                case Scheduletimes::EVERY_2H:
                    $interval = $this->calculateTimeInseconds(2, 0);
                    break;
                case Scheduletimes::EVERY_4H:
                    $interval = $this->calculateTimeInseconds(4, 0);
                    break;
                case Scheduletimes::EVERY_6H:
                    $interval = $this->calculateTimeInseconds(6, 0);
                    break;
                case Scheduletimes::EVERY_12H:
                    $interval = $this->calculateTimeInseconds(12, 0);
                    break;
                default:
                    $interval = $this->calculateTimeInseconds(12, 0);
                    break;
            }

            for ($timeInSeconds = $initialTime;$timeInSeconds < $maximumTime;$timeInSeconds = $timeInSeconds +$interval) {
                $runTimes[] = $timeInSeconds;
            }
        }

        //Prepare days
        $runDays = [];
        switch ($rule['days_interval']) {
                case Scheduledays::EVERY_DAY:
                    $runDays = [1, 2, 3, 4, 5, 6, 7];
                    break;
                case Scheduledays::EVERY_WEEKENDDAY:
                    $runDays = [6, 7];
                    break;
                case Scheduledays::EVERY_WORKDAY:
                    $runDays = [1, 2, 3, 4, 5];
                    break;
                default:
                    $runDays[] = $rule['days_interval'];
                    break;
        }

        foreach ($runDays as $dayOfWeek) {
            foreach ($runTimes as $dayTimeInSeconds) {
                $cronItems[$dayOfWeek . $dayTimeInSeconds] = [self::COL_DAY_OF_WEEK => $dayOfWeek, self::COL_TIME => $dayTimeInSeconds];
            }
        }

        return $cronItems;
    }

    protected function calculateTimeInseconds($hours, $minutes)
    {
        return $hours*self::HOUR_SECONDS + $minutes*self::MINUTE_SECONDS;
    }

    /**
     * Prepare interval(s) from last cron run.
     */
    protected function getScheduledIntervals()
    {
        $currentDateTime = $this->_datetimeFormat->getDateTime(null, true);
        $dayOfWeek = $this->_datetimeFormat->getDayOfWeek($currentDateTime);

        $timeFormated = $this->_datetimeFormat->getTime(null, true);
        $time = $this->_datetimeFormat->getTime();
        $lastRunTimeFormated = $this->helper->loadCache(\Nostress\Koongo\Helper\Data::CACHE_KEY_CRON_LAST_RUN);
        if (empty($lastRunTimeFormated)) {
            $lastRunTimeFormated = self::TIME_MIN;
        }

        $this->helper->saveCache($timeFormated, \Nostress\Koongo\Helper\Data::CACHE_KEY_CRON_LAST_RUN, [], \Nostress\Koongo\Helper\Data::CACHE_LIFETIME_KEY_CRON_LAST_RUN);
        $lastRunTime = strtotime($lastRunTimeFormated);
        $lastRunDayOfWeek = $dayOfWeek;

        if ($lastRunTime > $time) {
            $lastRunDayOfWeek--;
            if ($lastRunDayOfWeek < \Nostress\Koongo\Model\Config\Source\Scheduledays::MONDAY) {
                $lastRunDayOfWeek = \Nostress\Koongo\Model\Config\Source\Scheduledays::SUNDAY;
            }
        }

        $intervals = [];
        if ($lastRunDayOfWeek == $dayOfWeek) {
            $intervals[] = [self::TIME_FROM => $lastRunTimeFormated,self::TIME_TO => $timeFormated,self::DAY_OF_WEEK => $dayOfWeek];
        } else {
            $intervals[] = [self::TIME_FROM => $lastRunTimeFormated,self::TIME_TO => self::TIME_MAX,self::DAY_OF_WEEK => $lastRunDayOfWeek];
            $intervals[] = [self::TIME_FROM => self::TIME_MIN,self::TIME_TO => $timeFormated,self::DAY_OF_WEEK => $dayOfWeek];
        }

        //transform times to seconds
        foreach ($intervals as $index => $interval) {
            $time = $intervals[$index][self::TIME_FROM];
            $intervals[$index][self::TIME_FROM] = $seconds = strtotime("1970-01-01 $time UTC");

            $time = $intervals[$index][self::TIME_TO];
            $intervals[$index][self::TIME_TO] = $seconds = strtotime("1970-01-01 $time UTC");
        }

        return $intervals;
    }
}

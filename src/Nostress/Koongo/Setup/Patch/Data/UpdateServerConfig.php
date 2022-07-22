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

namespace Nostress\Koongo\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Nostress\Koongo\Model\Api\Client\Simple;

class UpdateServerConfig implements DataPatchInterface
{
    /**
     * @var Simple
     */
    protected $client;

    /**
     * @var ModuleDataSetupInterface
     */
    protected $moduleDataSetup;

    /**
     * @param Simple $client
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        Simple                   $client
    ) {
        $this->client = $client;
        $this->moduleDataSetup = $moduleDataSetup;
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }

    public function apply()
    {
        $this->moduleDataSetup->startSetup();
        $this->client->updateServerConfig(true);
        $this->moduleDataSetup->endSetup();
    }
}

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
        DELETE FROM setup_module WHERE `setup_module`.`module` = 'Nostress_Koongo';
        DROP TABLE nostress_koongo_cache_product;
        DROP TABLE nostress_koongo_cache_price;
        DROP TABLE nostress_koongo_cache_tax;
        DROP TABLE nostress_koongo_cache_categorypath;
        DROP TABLE nostress_koongo_cache_weee;
        DROP TABLE nostress_koongo_cache_profilecategory;
        DROP TABLE nostress_koongo_cache_channelcategory;
        DROP TABLE nostress_koongo_cache_pricetier;
        DROP TABLE nostress_koongo_cache_mediagallery;
        DROP TABLE nostress_koongo_taxonomy_category_mapping;
        DROP TABLE nostress_koongo_taxonomy_setup;
        DROP TABLE nostress_koongo_cron;
        DROP TABLE nostress_koongo_channel_profile;
        DROP TABLE nostress_koongo_channel_feed;
        DROP TABLE nostress_koongo_taxonomy_category;
        DROP TABLE nostress_koongo_webhook;
        DROP TABLE nostress_koongo_webhook_event;
        DROP TABLE nostress_koongo_cache_review;
        DROP TABLE nostress_koongo_cache_stock;
*/

namespace Nostress\Koongo\Setup;

class Uninstall implements \Magento\Framework\Setup\UninstallInterface
{
    /**
     * Module uninstall code
     *
     * @param \Magento\Framework\Setup\SchemaSetupInterface $setup
     * @param \Magento\Framework\Setup\ModuleContextInterface $context
     * @return void
     */
    public function uninstall(
        \Magento\Framework\Setup\SchemaSetupInterface $setup,
        \Magento\Framework\Setup\ModuleContextInterface $context
    ) {
        $setup->startSetup();
        $connection = $setup->getConnection();

        $connection->dropTable('nostress_koongo_cache_product');
        $connection->dropTable('nostress_koongo_cache_price');
        $connection->dropTable('nostress_koongo_cache_tax');
        $connection->dropTable('nostress_koongo_cache_categorypath');
        $connection->dropTable('nostress_koongo_cache_weee');
        $connection->dropTable('nostress_koongo_cache_profilecategory');
        $connection->dropTable('nostress_koongo_cache_channelcategory');
        $connection->dropTable('nostress_koongo_cache_mediagallery');
        $connection->dropTable('nostress_koongo_cache_pricetier');
        $connection->dropTable('nostress_koongo_taxonomy_category_mapping');
        $connection->dropTable('nostress_koongo_taxonomy_setup');
        $connection->dropTable('nostress_koongo_cron');
        $connection->dropTable('nostress_koongo_channel_profile');
        $connection->dropTable('nostress_koongo_channel_feed');
        $connection->dropTable('nostress_koongo_taxonomy_category');
        $connection->dropTable('nostress_koongo_webhook');
        $connection->dropTable('nostress_koongo_webhook_event');
        $connection->dropTable('nostress_koongo_cache_review');
        $connection->dropTable('nostress_koongo_cache_stock');

        $setup->endSetup();
    }
}

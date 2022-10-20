# Installation Procedure

Install process of this plugin requires ssh access on server where is hosted your store. 

## Download source
Download package via composer
```shell
composer require koongo-com/magento2-data-feed-manager
```

or download package from [releases](https://github.com/koongo-com/magento2-data-feed-manager/tags) 

## Install static files
Via script or copy files
```shell
php vendor/koongo-com/magento2-data-feed-manager install.php
# or
cp -R vendor/koongo-com/magento2-data-feed-manager/lib/* lib/
```

## Download source

```shell
php bin/magento module:enable Nostress_Koongo
php bin/magento setup:upgrade
php bin/magento setup:static:deploy
php bin/magento setup:di:compile
php bin/magento cache:flush
```



## Post-installation Steps
### Cache Refresh
Navigate to System → Cache Management and refresh ALL cache.

### Version with Enabled Flat Catalog
Koongo Connector can uses Flat Product and Flat Category data, so please make sure you have enabled these two settings. You can enable Flat by clicking on the link in the note or navigate to Stores -> Settings -> Configuration → System → Catalog → Storefront.
#### On Catalog page you must set this options:

* Use Flat Catalog Category = Yes
* Use Flat Catalog Product = Yes


#### Reindex Product and Category Flat Catalog
If your server cron job runs properly, Product and Category Flat Catalog will be re-indexed automatically.

If you'd like to turn the Flat Catalog off, you can force re-index via ssh by following command in your Magento 2 installation folder:

```shell
php bin/magento indexer:reindex
```

### Version without enabled Flat Catalog
You can use Magento 2 Connector with disabled Flat catalog.

#### If you want to deactivate the Flat Catalog, please add the command below into CRON:

```shell
php bin/magento koongo:flat:reindex
```
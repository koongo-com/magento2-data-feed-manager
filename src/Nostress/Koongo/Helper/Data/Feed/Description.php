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

namespace Nostress\Koongo\Helper\Data\Feed;

/**
 * Feed description Koongo connector Helper.
 *
 * @category Nostress
 * @package Nostress_Koongo
 *
 */
class Description extends \Nostress\Koongo\Helper\Data
{
    const TEXT = "text";
    const EXAMPLE = "example";
    const OPTIONS = "options";
    const FORMAT = "format";
    const DESCRIPTION = "description";

    protected $_initialized = false;
    protected $_defaultDescription = [
            "text" => "",
            "example" => "",
            "options" => "",
            "format" => "text"
    ];

    protected $_attributeDescription = [];

    public function getAttributeDescription($code)
    {
        $this->init();
        $description = $this->_getAttributeDescription($code);
        return [self::DESCRIPTION => $description];
    }

    protected function _getAttributeDescription($code)
    {
        $description = $this->_defaultDescription;
        if (array_key_exists($code, $this->_attributeDescription)) {
            $description = $this->updateArray($this->_attributeDescription[$code], $description);
        }
        return $description;
    }

    protected function init()
    {
        if ($this->_initialized) {
            return;
        }
        $this->defineAttributeDescriptions();
        $this->_initialized = true;
        return;
    }

    protected function addDesc($code, $desc)
    {
        foreach ($desc as $index => $value) {
            $desc[$index] = __($value);
        }

        $this->_attributeDescription[$code] = $desc;
    }

    protected function defineAttributeDescriptions()
    {
        $this->addDesc("short_description", [self::TEXT => "Short description of the item.",
                                            self::EXAMPLE =>""
                                            ]);

        $this->addDesc("description", [self::TEXT => "Description of the item. Include only information relevant to the item. Do not include any promotional text.",
                                            self::EXAMPLE =>"This teddy bear is fuzzy and cuddly. The bear is yellow with with white paws and stuffed with high quality hypoallergenic synthetic cotton."
                                            ]);
        $this->addDesc("name", [self::TEXT => "Title of the item. We recommend you include characteristics such as color or brand in the title which differentiates the item from other products.",
                                            self::EXAMPLE =>"Mens Pique Polo Shirt"
                                            ]);
        $this->addDesc("image", [self::TEXT => "URL of an image of the item",
                                            self::EXAMPLE =>"http://www.example.com/image1.jpg"
                                            ]);
        $this->addDesc("url", [self::TEXT => "URL directly linking to your item's page on your website",
                                            self::EXAMPLE =>"http://www.example.com/asp/sp.asp?cat=12&id=1030"
                                            ]);
        $this->addDesc("price_final_include_tax", [self::TEXT => "Advertised sale price of the item with tax.",
                                            self::EXAMPLE =>""
                                            ]);
        $this->addDesc("price_final_exclude_tax", [self::TEXT => "Advertised sale price of the item without tax.",
                                            self::EXAMPLE =>""
                                            ]);
        $this->addDesc("price_original_include_tax", [self::TEXT => "Original price of the item with tax.",
                                            self::EXAMPLE =>""
                                            ]);
        $this->addDesc("price_original_exclude_tax", [self::TEXT => "Original price of the item without tax.",
                                            self::EXAMPLE =>""
                                            ]);
        $this->addDesc("price_discount_percent", [self::TEXT => "Amount of discount in percent.",
                                            self::EXAMPLE =>""
                                            ]);
        $this->addDesc("price_discount_exclude_tax", [self::TEXT => "Discount amount without tax.",
                                            self::EXAMPLE =>""
                                            ]);
        $this->addDesc("price_discount_include_tax", [self::TEXT => "Discount amount with tax.",
                                            self::EXAMPLE =>""
                                            ]);
        $this->addDesc("gtin", [self::TEXT => "Global Trade Item Number (GTIN) of the item. These identifiers include UPC (in North America), EAN (in Europe), JAN (in Japan), and ISBN (for books).",
                                            self::EXAMPLE =>""
                                            ]);
        $this->addDesc("upc", [self::TEXT => "The Universal Product Code (UPC). A barcode symbology, that is widely used in North America, and in countries including the UK, Australia, and New Zealand for tracking trade items.",
                                            self::EXAMPLE =>"123456789999"
                                            ]);
        $this->addDesc("ean", [self::TEXT => "International Article Number(EAN). 13 digit barcode.",
                                            self::EXAMPLE =>"5901234123457"
                                            ]);
        $this->addDesc("isbn", [self::TEXT => "International Standard Book Number(ISBN). Unique numeric commercial book identifier.",
                                            self::EXAMPLE => "978-3-16-148410-0"
                                            ]);
        $this->addDesc("jan", [self::TEXT => "Japanese Article Number (JAN) is a barcode standard compatible with the European Article Number scheme",
                                            self::EXAMPLE =>""
                                            ]);
        $this->addDesc("mpn", [self::TEXT => "Manufacturer Part Number (MPN) of the item. Code uniquely identifies the product to its manufacturer.",
                                            self::EXAMPLE =>"GO12345OOGLE"
                                            ]);
        $this->addDesc("manufacturer", [self::TEXT => "Brand of the item.",
                                            self::EXAMPLE =>"Sony"
                                            ]);
        $this->addDesc("size", [self::TEXT => "Size of item.",
                                            self::EXAMPLE =>"XL"
                                            ]);
        $this->addDesc("color", [self::TEXT => "Color of the item.",
                                            self::EXAMPLE =>"Red"
                                            ]);
        $this->addDesc("gender", [self::TEXT => "Gender of the item.",
                                            self::EXAMPLE =>"Female"
                                            ]);
        $this->addDesc("weight", [self::TEXT => "Weight of the item.",
                                            self::EXAMPLE =>"1.2"
                                            ]);
        $this->addDesc("age_group", [self::TEXT => "Age group of the item.",
                                            self::EXAMPLE =>"Adults"
                                            ]);
        $this->addDesc("tax_percent", [self::TEXT => "Item tax rate.",
                                            self::EXAMPLE =>"20"
                                            ]);
        $this->addDesc("shipping_method_price", [self::TEXT => "Fixed delivery price.",
                                            self::EXAMPLE =>""
                                            ]);
        $this->addDesc("shipping_method_name", [self::TEXT => "Name of shipping method.",
                                            self::EXAMPLE =>""
                                            ]);
        $this->addDesc("delivery_time", [self::TEXT => "Delivery time - period to deliver item to a customer.",
                                            self::EXAMPLE => "Within 2-3 days"
                                            ]);
        $this->addDesc("stock_status", [self::TEXT => "Availability of item in stock.",
                                            self::EXAMPLE =>"In stock, On request"
                                            ]);
        $this->addDesc("category_name", [self::TEXT => "Name of the lowest category, where the product is located.",
                                            self::EXAMPLE => ""
                                            ]);
        $this->addDesc("category_id", [self::TEXT => "Id of the lowest category, where the product is located.",
                                            self::EXAMPLE => ""
                                            ]);
        $this->addDesc("category_parent_name", [self::TEXT => "Name of the second lowest category, where the product is located.",
                                            self::EXAMPLE => ""
                                            ]);
        $this->addDesc("category_parent_id", [self::TEXT => "Id of the second lowest category, where the product is located.",
                                            self::EXAMPLE => ""
                                            ]);
        $this->addDesc("category_path", [self::TEXT => "Path of the category, where the product is located.",
                                            self::EXAMPLE => ""
                                            ]);
        $this->addDesc("taxonomy_name", [self::TEXT => "Name of the lowest taxonomy category, where the product is located.",
                                            self::EXAMPLE => ""
                                            ]);
        $this->addDesc("taxonomy_id", [self::TEXT => "Id of the lowest taxonomy category, where the product is located.",
                                            self::EXAMPLE => ""
                                            ]);
        $this->addDesc("taxonomy_path", [self::TEXT => "Path of the taxonomy, where the product is located.",
                                            self::EXAMPLE => ""
                                            ]);
        $this->addDesc("qty", [self::TEXT => "Number of products in stock",
                                            self::EXAMPLE =>"50"
                                            ]);
        $this->addDesc("currency", [self::TEXT => "Currency for item prices.",
                                            self::EXAMPLE =>"USD"
                                            ]);
        $this->addDesc("sku", [self::TEXT => "Stock-keeping unit, a unique identifier for each distinct product and service that can be purchased in business.",
                                            self::EXAMPLE =>""
                                            ]);
        $this->addDesc("reviews_count", [self::TEXT => "The number of participants in discussion about one item.",
                                            self::EXAMPLE =>""
                                            ]);
        $this->addDesc("reviews_url", [self::TEXT => "Url link to product discussion.",
                                            self::EXAMPLE =>""
                                            ]);
        $this->addDesc("condition", [self::TEXT => "Item condition.",
                                            self::EXAMPLE =>"new"
                                            ]);
        $this->addDesc("group_id", [self::TEXT => "Items group id. Based on parent product id.",
                                            self::EXAMPLE =>""
                                            ]);
        $this->addDesc("id", [self::TEXT => "Unique item id.",
                                            self::EXAMPLE =>""
                                            ]);
        $this->addDesc("update_date", [self::TEXT => "Last product update date.",
                                            self::EXAMPLE =>""
                                            ]);
        $this->addDesc("update_datetime", [self::TEXT => "Last product update date and time.",
                                            self::EXAMPLE =>""
                                            ]);
        $this->addDesc("update_time", [self::TEXT => "Last product update time.",
                                            self::EXAMPLE =>""
                                            ]);
        $this->addDesc("creation_date", [self::TEXT => "Product creation date.",
                                            self::EXAMPLE =>""
                                            ]);
        $this->addDesc("creation_datetime", [self::TEXT => "Product creation date and time.",
                                            self::EXAMPLE =>""
                                            ]);
        $this->addDesc("creation_time", [self::TEXT => "Product creation time.",
                                            self::EXAMPLE =>""
                                            ]);
    }
}

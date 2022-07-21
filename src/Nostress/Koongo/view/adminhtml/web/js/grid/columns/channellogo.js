
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

define([
    'Magento_Ui/js/grid/columns/column',
    'jquery',
    'mage/template',
    //'text!Nostress_Koongo/templates/grid/cells/channellogo/preview.html',
    'text!Magento_Ui/templates/grid/cells/thumbnail/preview.html',
    'Magento_Ui/js/modal/modal'
], function (Column, $, mageTemplate, thumbnailPreviewTemplate) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'ui/grid/cells/thumbnail',
        	//bodyTmpl: 'Nostress_Koongo/templates/grid/cells/channellogo', //nefunguje
            fieldClass: {
                //'data-grid-channellogo-cell': true  //tohle funguje - pridat do valstniho css
                'data-grid-thumbnail-cell': true
            }
        },
        getSrc: function (row) {
            return row[this.index + '_src']
        },
        getOrigSrc: function (row) {
            return row[this.index + '_orig_src'];
        },
        getLink: function (row) {
            return row[this.index + '_link'];
        },
        getAlt: function (row) {
            return row[this.index + '_alt']
        },
        isPreviewAvailable: function() {
            return this.has_preview || false;
        },
        preview: function (row) {
            var modalHtml = mageTemplate(
                thumbnailPreviewTemplate,
                {
                    src: this.getOrigSrc(row), alt: this.getAlt(row), link: this.getLink(row),
                    linkText: ''//$.mage.__('Go to Details Page')
                }
            );
            var previewPopup = $('<div/>').html(modalHtml);
            previewPopup.modal({
                title: this.getAlt(row),
                innerScroll: true,
                modalClass: '_image-box',
                buttons: []}).trigger('openModal');
        },
        getFieldHandler: function (row) {
            if (this.isPreviewAvailable()) {
                return this.preview.bind(this, row);
            }
        }
    });
});

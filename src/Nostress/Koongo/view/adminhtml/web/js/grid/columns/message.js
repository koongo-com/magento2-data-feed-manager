
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
        'text!Nostress_Koongo/templates/grid/cells/profile/message.html',
        'Magento_Ui/js/modal/modal'
    ], function (Column, $, mageTemplate, messagePreviewTemplate) {
        'use strict';
     
        return Column.extend({
            defaults: {
                bodyTmpl: 'ui/grid/cells/html',
                fieldClass: {
                    'data-grid-html-cell': true
                }
            },
            gethtml: function (row) {
                return row[this.index + '_html'];
            },
            getMessage: function (row) {
                return row[this.index + '_message'];
            },
            getStatus: function (row) {
                return row[this.index + '_status'];
            },
            getLabel: function (row) {
                return row[this.index + '_html']
            },
            getTitle: function (row) {
                return row[this.index + '_title']
            },            
            
            preview: function (row) {
                var modalHtml = mageTemplate(
                		messagePreviewTemplate,
                    {
                        html: this.gethtml(row), 
                        title: this.getTitle(row), 
                        label: this.getLabel(row), 
                        status: this.getStatus(row),
                        message: this.getMessage(row)                        
                    }
                );
                var modalHtmlWithMessageHtml = modalHtml.replace('{{message_html_content}}',this.getMessage(row));
                var previewPopup = $('<div/>').html(modalHtmlWithMessageHtml);
                previewPopup.modal({
                    title: this.getTitle(row),
                    innerScroll: true,
                    modalClass: '_image-box',
                    buttons: []}).trigger('openModal');
            },
            getFieldHandler: function (row) {
                return this.preview.bind(this, row);
            }
        });
    });

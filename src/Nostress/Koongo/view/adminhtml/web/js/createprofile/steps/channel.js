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
// jscs:disable jsDoc
define([
    'uiComponent',
    'jquery',
    'ko',
    'underscore',
    'mage/translate'
], function (Component, $, ko, _) {
    'use strict';
    
    var self;

    return Component.extend({
        defaults: {
             notificationMessage: {
                text: null,
                error: null
            },                        
            feedLink: '',
            storeId: '',
            isChecked: '',
            channelsByLink: '',
            channel: '',
            channels: [],
            stores: []
        },
                
        updateChannel: function( value) {        	        	        	
        	
        	// bind feedLink
        	self.feedLink(value);
        	
        	//channel list not loaded
        	if(!self.channelsByLink)
        		return this;
        	
        	if( !self.channels[ value]) {
	        	$.get( self.channelsByLink[ value].description, function(data) { 
	        	    // Now use this data to update your view models, 
	        	    // and Knockout will update your UI automatically
	        		
	        		self.channels[ self.feedLink()] = self.channelsByLink[ value];
	        		self.channels[ self.feedLink()].description = data;
	        		self.channels[ self.feedLink()].link = value;
	        		self.channel( self.channels[ self.feedLink()]);
	        	});
        	} else {
        		self.channel( self.channels[ self.feedLink()]);
        	}
        	
        	return this;
        },
        
        initObservable: function () {
        	this._super().observe('feedLink storeId isChecked channel'); 
        	
        	this.isChecked = ko.observable(false);        	
        	
            return this;
        },
        
        initialize: function() {
        	this._super();
        	
        	self = this;
        	
        	this.isChecked.subscribe( this.updateChannel);             	
        	
        	this.isChecked( this.feedLink());
        },
        
        nextLabelText: $.mage.__('Next'),
        variations: [],

        render: function (wizard) {
            this.wizard = wizard;
            //this.sections(wizard.data.sections());
            //this.attributes(wizard.data.attributes());      
        },
        force: function (wizard) 
        {              	
        	wizard.data.feedLink = this.feedLink;
        	wizard.data.storeId = this.storeId;
        //    this.variationsComponent().render(this.variations, this.attributes());
        //    $('[data-role=step-wizard-dialog]').trigger('closeModal');
        },
        back: function () {
        }        
    });
});

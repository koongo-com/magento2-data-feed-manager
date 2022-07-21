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
    'mage/translate',
    'mage/template',
    'text!Nostress_Koongo/templates/tooltip.html',
    'Magento_Ui/js/modal/modal',
], function (Component, $, ko, _, __, mageTemplate, tooltipTemplate) {
    'use strict';
    
    var self;

    return Component.extend({
    	
    	defaults: {
            rules: [],
            ruleSource: []            
        },
        
        initObservable: function () 
        {   
        	self = this;        	
        	this._super().observe('currentIndex');       	        	        	
        	this.rules = ko.observableArray([]);      	      	        	

        	for (var i = 0; i < this.ruleSource.length; i++)        	
        	{    
        		this.rules.push(new RuleItem(this.ruleSource[i].days_interval, this.ruleSource[i].times_interval, this.ruleSource[i].enabled, this.ruleSource[i].time_hours, this.ruleSource[i].time_minutes));
        	}    
        	
            return this;
        },        
        
                               
        addRuleTableRow: function() 
        {
        	var index = this.rules.push(new RuleItem('','',true,'1',0));        	
        },
          
        removeRuleTableRow: function(row, self) 
        {        	
        	self.rules.remove(row); 
        },
        
        duplicateRuleTableRow: function(row, self, index) 
        {      
        	this.rules.splice(index,0,new RuleItem(row.days_interval() ,row.times_interval() ,row.enabled(),row.time_hours(),row.time_minutes() ));         	 
        },                
                
    });        
    
    function RuleItem(days_interval, time_interval,enabled, time_hours, time_minutes) 
    {
        var self = this;  
        self.days_interval = ko.observable(days_interval);   
        self.times_interval = ko.observable(time_interval);
        self.enabled = ko.observable(enabled); 
        self.time_hours = ko.observable(time_hours);
        self.time_minutes = ko.observable(time_minutes);
    };
});

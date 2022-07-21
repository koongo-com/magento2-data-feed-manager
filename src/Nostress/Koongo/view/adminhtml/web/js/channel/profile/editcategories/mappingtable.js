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
    
    var accent_map = {
    	'\n':' ','ẚ':'a','Á':'a','á':'a','À':'a','à':'a','Ă':'a','ă':'a','Ắ':'a','ắ':'a','Ằ':'a','ằ':'a','Ẵ':'a','ẵ':'a','Ẳ':'a','ẳ':'a','Â':'a','â':'a','Ấ':'a','ấ':'a','Ầ':'a','ầ':'a','Ẫ':'a','ẫ':'a','Ẩ':'a','ẩ':'a','Ǎ':'a','ǎ':'a','Å':'a','å':'a','Ǻ':'a','ǻ':'a','Ä':'a','ä':'a','Ǟ':'a','ǟ':'a','Ã':'a','ã':'a','Ȧ':'a','ȧ':'a','Ǡ':'a','ǡ':'a','Ą':'a','ą':'a','Ā':'a','ā':'a','Ả':'a','ả':'a','Ȁ':'a','ȁ':'a','Ȃ':'a','ȃ':'a','Ạ':'a','ạ':'a','Ặ':'a','ặ':'a','Ậ':'a','ậ':'a','Ḁ':'a','ḁ':'a','Ⱥ':'a','ⱥ':'a','Ǽ':'a','ǽ':'a','Ǣ':'a','ǣ':'a','Ḃ':'b','ḃ':'b','Ḅ':'b','ḅ':'b','Ḇ':'b','ḇ':'b','Ƀ':'b','ƀ':'b','ᵬ':'b','Ɓ':'b','ɓ':'b','Ƃ':'b','ƃ':'b','Ć':'c','ć':'c','Ĉ':'c','ĉ':'c','Č':'c','č':'c','Ċ':'c','ċ':'c','Ç':'c','ç':'c','Ḉ':'c','ḉ':'c','Ȼ':'c','ȼ':'c','Ƈ':'c','ƈ':'c','ɕ':'c','Ď':'d','ď':'d','Ḋ':'d','ḋ':'d','Ḑ':'d','ḑ':'d','Ḍ':'d','ḍ':'d','Ḓ':'d','ḓ':'d','Ḏ':'d','ḏ':'d','Đ':'d','đ':'d','ᵭ':'d','Ɖ':'d','ɖ':'d','Ɗ':'d','ɗ':'d','Ƌ':'d','ƌ':'d','ȡ':'d','ð':'d','É':'e','Ə':'e','Ǝ':'e','ǝ':'e','é':'e','È':'e','è':'e','Ĕ':'e','ĕ':'e','Ê':'e','ê':'e','Ế':'e','ế':'e','Ề':'e','ề':'e','Ễ':'e','ễ':'e','Ể':'e','ể':'e','Ě':'e','ě':'e','Ë':'e','ë':'e','Ẽ':'e','ẽ':'e','Ė':'e','ė':'e','Ȩ':'e','ȩ':'e','Ḝ':'e','ḝ':'e','Ę':'e','ę':'e','Ē':'e','ē':'e','Ḗ':'e','ḗ':'e','Ḕ':'e','ḕ':'e','Ẻ':'e','ẻ':'e','Ȅ':'e','ȅ':'e','Ȇ':'e','ȇ':'e','Ẹ':'e','ẹ':'e','Ệ':'e','ệ':'e','Ḙ':'e','ḙ':'e','Ḛ':'e','ḛ':'e','Ɇ':'e','ɇ':'e','ɚ':'e','ɝ':'e','Ḟ':'f','ḟ':'f','ᵮ':'f','Ƒ':'f','ƒ':'f','Ǵ':'g','ǵ':'g','Ğ':'g','ğ':'g','Ĝ':'g','ĝ':'g','Ǧ':'g','ǧ':'g','Ġ':'g','ġ':'g','Ģ':'g','ģ':'g','Ḡ':'g','ḡ':'g','Ǥ':'g','ǥ':'g','Ɠ':'g','ɠ':'g','Ĥ':'h','ĥ':'h','Ȟ':'h','ȟ':'h','Ḧ':'h','ḧ':'h','Ḣ':'h','ḣ':'h','Ḩ':'h','ḩ':'h','Ḥ':'h','ḥ':'h','Ḫ':'h','ḫ':'h','H':'h','̱':'h','ẖ':'h','Ħ':'h','ħ':'h','Ⱨ':'h','ⱨ':'h','Í':'i','í':'i','Ì':'i','ì':'i','Ĭ':'i','ĭ':'i','Î':'i','î':'i','Ǐ':'i','ǐ':'i','Ï':'i','ï':'i','Ḯ':'i','ḯ':'i','Ĩ':'i','ĩ':'i','İ':'i','i':'i','Į':'i','į':'i','Ī':'i','ī':'i','Ỉ':'i','ỉ':'i','Ȉ':'i','ȉ':'i','Ȋ':'i','ȋ':'i','Ị':'i','ị':'i','Ḭ':'i','ḭ':'i','I':'i','ı':'i','Ɨ':'i','ɨ':'i','Ĵ':'j','ĵ':'j','J':'j','̌':'j','ǰ':'j','ȷ':'j','Ɉ':'j','ɉ':'j','ʝ':'j','ɟ':'j','ʄ':'j','Ḱ':'k','ḱ':'k','Ǩ':'k','ǩ':'k','Ķ':'k','ķ':'k','Ḳ':'k','ḳ':'k','Ḵ':'k','ḵ':'k','Ƙ':'k','ƙ':'k','Ⱪ':'k','ⱪ':'k','Ĺ':'a','ĺ':'l','Ľ':'l','ľ':'l','Ļ':'l','ļ':'l','Ḷ':'l','ḷ':'l','Ḹ':'l','ḹ':'l','Ḽ':'l','ḽ':'l','Ḻ':'l','ḻ':'l','Ł':'l','ł':'l','Ł':'l','̣':'l','ł':'l','̣':'l','Ŀ':'l','ŀ':'l','Ƚ':'l','ƚ':'l','Ⱡ':'l','ⱡ':'l','Ɫ':'l','ɫ':'l','ɬ':'l','ɭ':'l','ȴ':'l','Ḿ':'m','ḿ':'m','Ṁ':'m','ṁ':'m','Ṃ':'m','ṃ':'m','ɱ':'m','Ń':'n','ń':'n','Ǹ':'n','ǹ':'n','Ň':'n','ň':'n','Ñ':'n','ñ':'n','Ṅ':'n','ṅ':'n','Ņ':'n','ņ':'n','Ṇ':'n','ṇ':'n','Ṋ':'n','ṋ':'n','Ṉ':'n','ṉ':'n','Ɲ':'n','ɲ':'n','Ƞ':'n','ƞ':'n','ɳ':'n','ȵ':'n','N':'n','̈':'n','n':'n','̈':'n','Ó':'o','ó':'o','Ò':'o','ò':'o','Ŏ':'o','ŏ':'o','Ô':'o','ô':'o','Ố':'o','ố':'o','Ồ':'o','ồ':'o','Ỗ':'o','ỗ':'o','Ổ':'o','ổ':'o','Ǒ':'o','ǒ':'o','Ö':'o','ö':'o','Ȫ':'o','ȫ':'o','Ő':'o','ő':'o','Õ':'o','õ':'o','Ṍ':'o','ṍ':'o','Ṏ':'o','ṏ':'o','Ȭ':'o','ȭ':'o','Ȯ':'o','ȯ':'o','Ȱ':'o','ȱ':'o','Ø':'o','ø':'o','Ǿ':'o','ǿ':'o','Ǫ':'o','ǫ':'o','Ǭ':'o','ǭ':'o','Ō':'o','ō':'o','Ṓ':'o','ṓ':'o','Ṑ':'o','ṑ':'o','Ỏ':'o','ỏ':'o','Ȍ':'o','ȍ':'o','Ȏ':'o','ȏ':'o','Ơ':'o','ơ':'o','Ớ':'o','ớ':'o','Ờ':'o','ờ':'o','Ỡ':'o','ỡ':'o','Ở':'o','ở':'o','Ợ':'o','ợ':'o','Ọ':'o','ọ':'o','Ộ':'o','ộ':'o','Ɵ':'o','ɵ':'o','Ṕ':'p','ṕ':'p','Ṗ':'p','ṗ':'p','Ᵽ':'p','Ƥ':'p','ƥ':'p','P':'p','̃':'p','p':'p','̃':'p','ʠ':'q','Ɋ':'q','ɋ':'q','Ŕ':'r','ŕ':'r','Ř':'r','ř':'r','Ṙ':'r','ṙ':'r','Ŗ':'r','ŗ':'r','Ȑ':'r','ȑ':'r','Ȓ':'r','ȓ':'r','Ṛ':'r','ṛ':'r','Ṝ':'r','ṝ':'r','Ṟ':'r','ṟ':'r','Ɍ':'r','ɍ':'r','ᵲ':'r','ɼ':'r','Ɽ':'r','ɽ':'r','ɾ':'r','ᵳ':'r','ß':'s','Ś':'s','ś':'s','Ṥ':'s','ṥ':'s','Ŝ':'s','ŝ':'s','Š':'s','š':'s','Ṧ':'s','ṧ':'s','Ṡ':'s','ṡ':'s','ẛ':'s','Ş':'s','ş':'s','Ṣ':'s','ṣ':'s','Ṩ':'s','ṩ':'s','Ș':'s','ș':'s','ʂ':'s','S':'s','̩':'s','s':'s','̩':'s','Þ':'t','þ':'t','Ť':'t','ť':'t','T':'t','̈':'t','ẗ':'t','Ṫ':'t','ṫ':'t','Ţ':'t','ţ':'t','Ṭ':'t','ṭ':'t','Ț':'t','ț':'t','Ṱ':'t','ṱ':'t','Ṯ':'t','ṯ':'t','Ŧ':'t','ŧ':'t','Ⱦ':'t','ⱦ':'t','ᵵ':'t','ƫ':'t','Ƭ':'t','ƭ':'t','Ʈ':'t','ʈ':'t','ȶ':'t','Ú':'u','ú':'u','Ù':'u','ù':'u','Ŭ':'u','ŭ':'u','Û':'u','û':'u','Ǔ':'u','ǔ':'u','Ů':'u','ů':'u','Ü':'u','ü':'u','Ǘ':'u','ǘ':'u','Ǜ':'u','ǜ':'u','Ǚ':'u','ǚ':'u','Ǖ':'u','ǖ':'u','Ű':'u','ű':'u','Ũ':'u','ũ':'u','Ṹ':'u','ṹ':'u','Ų':'u','ų':'u','Ū':'u','ū':'u','Ṻ':'u','ṻ':'u','Ủ':'u','ủ':'u','Ȕ':'u','ȕ':'u','Ȗ':'u','ȗ':'u','Ư':'u','ư':'u','Ứ':'u','ứ':'u','Ừ':'u','ừ':'u','Ữ':'u','ữ':'u','Ử':'u','ử':'u','Ự':'u','ự':'u','Ụ':'u','ụ':'u','Ṳ':'u','ṳ':'u','Ṷ':'u','ṷ':'u','Ṵ':'u','ṵ':'u','Ʉ':'u','ʉ':'u','Ṽ':'v','ṽ':'v','Ṿ':'v','ṿ':'v','Ʋ':'v','ʋ':'v','Ẃ':'w','ẃ':'w','Ẁ':'w','ẁ':'w','Ŵ':'w','ŵ':'w','W':'w','̊':'w','ẘ':'w','Ẅ':'w','ẅ':'w','Ẇ':'w','ẇ':'w','Ẉ':'w','ẉ':'w','Ẍ':'x','ẍ':'x','Ẋ':'x','ẋ':'x','Ý':'y','ý':'y','Ỳ':'y','ỳ':'y','Ŷ':'y','ŷ':'y','Y':'y','̊':'y','ẙ':'y','Ÿ':'y','ÿ':'y','Ỹ':'y','ỹ':'y','Ẏ':'y','ẏ':'y','Ȳ':'y','ȳ':'y','Ỷ':'y','ỷ':'y','Ỵ':'y','ỵ':'y','ʏ':'y','Ɏ':'y','ɏ':'y','Ƴ':'y','ƴ':'y','Ź':'z','ź':'z','Ẑ':'z','ẑ':'z','Ž':'z','ž':'z','Ż':'z','ż':'z','Ẓ':'z','ẓ':'z','Ẕ':'z','ẕ':'z','Ƶ':'z','ƶ':'z','Ȥ':'z','ȥ':'z','ʐ':'z','ʑ':'z','Ⱬ':'z','ⱬ':'z','Ǯ':'z','ǯ':'z','ƺ':'z','２':'2','６':'6','Ｂ':'B','Ｆ':'F','Ｊ':'J','Ｎ':'N','Ｒ':'R','Ｖ':'V','Ｚ':'Z','ｂ':'b','ｆ':'f','ｊ':'j','ｎ':'n','ｒ':'r','ｖ':'v','ｚ':'z','１':'1','５':'5','９':'9','Ａ':'A','Ｅ':'E','Ｉ':'I','Ｍ':'M','Ｑ':'Q','Ｕ':'U','Ｙ':'Y','ａ':'a','ｅ':'e','ｉ':'i','ｍ':'m','ｑ':'q','ｕ':'u','ｙ':'y','０':'0','４':'4','８':'8','Ｄ':'D','Ｈ':'H','Ｌ':'L','Ｐ':'P','Ｔ':'T','Ｘ':'X','ｄ':'d','ｈ':'h','ｌ':'l','ｐ':'p','ｔ':'t','ｘ':'x','３':'3','７':'7','Ｃ':'C','Ｇ':'G','Ｋ':'K','Ｏ':'O','Ｓ':'S','Ｗ':'W','ｃ':'c','ｇ':'g','ｋ':'k','ｏ':'o','ｓ':'s','ｗ':'w'
	};
    
    var self;

	String.prototype.accentFold = function () {		
		var s = this.toString();
	    if (!s) { return ''; }

	    var ret = '';
	    for (var i = 0; i < s.length; i++) {
	        ret += accent_map[s.charAt(i)] || s.charAt(i);
	    }
	    return ret;
	};

    return Component.extend({
    	
    	defaults: {
            rules: [],
            ruleSource: [],
            currentIndex: '-1',
            magentoCategories: [],
            channelCategories: [],
            channelCategoriesSource: [],
            channelCategoriesSearchItems: [],
            channelCategoriesSearchQuery: "",
            searchQueryDelimiter: "||",
            loadDataUrl: "",
            previewUrl: "",
            previewHelpUrl: "",
            //maximal number of item found per search
            maxFoundItems: 25,
            //minimal length of word to be used for string compare
            minWordLength: 3,
        },
        
        initObservable: function () 
        {   
        	self = this;
        	
        	this._super().observe('currentIndex channelCategoriesSearchQuery');        	
        	
        	//this.attributes = ko.observableArray(this.attributeSource);      
        	this.channelCategoriesSearchItems = ko.observableArray([]);        	
        	
        	//update search when search query changed
        	self.channelCategoriesSearchQuery.subscribe(function (newText) {
        		self.updateSearchChannelCategories(newText,self, true);
        	});
        	
        	this.rules = ko.observableArray([]);
        	
        	$('#preview_button').click( function() {
        		self.preview( $('#channel_profile_category_locale').val());
        	});
        	
        	$('#channel_profile_category_locale').change( function() {
        		
        		self.saveData();
        		
        		self.loadData( $(this).val());
        	});        	
        	
        	this.loadData( $('#channel_profile_category_locale').val());
        	
            return this;
        },        
        
        saveData: function( showLoader, callbackFunction) {
        	
        	if( showLoader == 'undefined') {
        		showLoader = false;
        	}
        	
        	var form = $('#edit_form');        	
        	$.ajax({
            	method: 'post',
        	    url: form.attr( 'action'),
        	    data: form.serialize(),
        	    showLoader: showLoader
        	}).done(function( data ) {
        		if( data.error) {
        			alert( data.message);
        		} else {
        			// nothing. May be some notice
        			if( callbackFunction) {
        				callbackFunction();
        			}
        		}        		            
    	    });
        },
        
        loadData: function( locale) {        
        	
        	var postData = { 'taxonomy_locale': locale};
        	
        	if( typeof self.channelCategoriesSource[locale] !== "object") {
        		postData.with_categories =  true;
        	}
        	
        	$.ajax({
            	method: 'post',
        	    url: self.loadDataUrl,
        	    data: postData,
        	    showLoader: true, // enable loader
        	    dataType: 'json'
        	}).done(function( data ) {
        		if( data.error) {
        			alert( data.message);
        		} else {
        			if( data.channel_categories) {
        				self.channelCategoriesSource[locale] = data.channel_categories;
        			}
        			self.ruleSource = data.mapping_rules;        			
        		}
        		self.channelCategories = self.channelCategoriesSource[locale];        		
        		self.rules.removeAll();
        		for (var i = 0; i < self.ruleSource.length; i++)        	
            	{    
            		self.rules.push(new RuleItem(
        				self.ruleSource[i].magento_categories, 
        				self.ruleSource[i].channel_category,
        				self.magentoCategories
            	    ));
            	}
        		// keep locale value for form saving
        		$('#current_channel_categories_locale').val( locale);
    	    });
        },
        
        openSettings: function (index) 
        {             
        	this.currentIndex(index);       	        	        	
        	var nodeIdsString = this.rules()[index].magentoCategoryIds();
        	var nodeIds = [];
        	if(nodeIdsString)
        		nodeIds = nodeIdsString.replace(/ /g, "").split(","); //Replace spaces and split by comma
        	tree.checkNodes(nodeIds);
        	
        	//init channel category searchbox
        	this.channelCategoriesSearchQuery("");
        	this.searchChannelCategories();
        	
        	$('[data-role=mapping-table-settings-dialog]').trigger('openModal');           
        },
        close: function () 
        {        	
            $('[data-role=mapping-table-settings-dialog]').trigger('closeModal');
        },                      
        
        addRuleTableRow: function() 
        {
        	var index = this.rules.push(new RuleItem('','',this.magentoCategories));
        	this.openSettings( index - 1);
        },
          
        removeRuleTableRow: function(row, self) 
        {        	
        	self.rules.remove(row); 
        },
        
        duplicateRuleTableRow: function(row, self, index) 
        {      
        	this.rules.splice(index,0,new RuleItem(row.magentoCategoryIds() ,row.channelCategoryId() ,this.magentoCategories ));         	 
        },                
        
        setChannelCategory: function(categoryItem, self){
        	var index = self.currentIndex();
        	self.rules()[index].channelCategoryId(categoryItem.hash);        	
        },        
        
        updateSearchChannelCategories: function(searchQuery, self, fullTextSearch){
        	if(searchQuery != "")
        	{
        		this.channelCategoriesSearchQuery(searchQuery.trim());
        		self.searchChannelCategories(fullTextSearch);
        	}
        },
        
        isChannelCategorySelected: function() {
        	return (this.currentIndex() != '-1' && this.rules()[this.currentIndex()] && this.rules()[this.currentIndex()].channelCategoryId() != '');
        },        
        
        getChannelCategory: function() {
        	
        	var no_category_label = 'No category selected. Search and choose from suggestions.';
        	
        	if( this.isChannelCategorySelected()) {
        		return this.channelCategories[this.rules()[this.currentIndex()].channelCategoryId()].path;
        	} else {
        		return no_category_label;        		
        	}
        },
        getChannelCategoryClass: function() {
        	if( this.isChannelCategorySelected()) {
        		return 'channel-category selected';
        	} else {
        		return 'channel-category';
        	}
        },
        
        searchChannelCategories: function(fullTextSearch = true) {
        	
        	// remove all searched items
            this.channelCategoriesSearchItems.removeAll();
            var value = this.channelCategoriesSearchQuery();

            var maxItems = this.maxFoundItems;
            
            //Search value is empty
            if(value === "")
            {
            	//add first level categries, show just first level
                if(this.channelCategoriesSearchItems().length <= 0)
                {
              	  for(var itemIndex in this.channelCategories) 
      	          {	            	
      	           	var item = this.channelCategories[itemIndex];
      	           	
      	           	if(item.name == item.path) 
      	           	{
      	           		this.channelCategoriesSearchItems.push(item);
      	           	}
      	          }
                }
                
                if( this.channelCategoriesSearchItems().length == 0) {
                	$('ul.suggestions').hide();
                }   
                
                return;
            }
            	
            //Split multiple search words
            var searchItems = [];
            if(value.indexOf(this.searchQueryDelimiter) >= 0)
            	searchItems = value.split(this.searchQueryDelimiter);
            else
            	searchItems.push(value);                       
            
            var selectedCategoryIds = [];
            
            var searchItemsFolded = [];
            for(var searchItemIndex = 0; searchItemIndex < searchItems.length;searchItemIndex++)
            {
            	var searchItem = searchItems[searchItemIndex];
            	searchItemsFolded.push( searchItem.toString().accentFold().toLowerCase());	            
            }
            
          //select categories whose name contain given search word
            var selectedChildIds = [];
            for(var searchItemIndex = 0; searchItemIndex < searchItemsFolded.length; searchItemIndex++)            
            {
            	var searchItem = searchItemsFolded[searchItemIndex];
            	if(!fullTextSearch || searchItem.length > this.minWordLength)
            	{
		            for(var itemIndex in this.channelCategories) 
		            {	            	
		            	var item = this.channelCategories[itemIndex];
		            	if(item.name_folded == searchItem) 
		            	{
		            		this.channelCategoriesSearchItems.push(item);
		            		selectedCategoryIds.push(item.id);	            		
		            	}
		            	
		            	//select direct child categories of selected categories
		            	if(selectedCategoryIds.indexOf(item.parent_id) >= 0 && selectedChildIds.indexOf(item.id) < 0)
		            	{
		            		var aa = selectedCategoryIds.indexOf(item.id);
		            		self.channelCategoriesSearchItems.push(item);
		            		selectedChildIds.push(item.id);	           		
		            	}
		            	
		            	if(this.channelCategoriesSearchItems().length >= maxItems)
	            		{
	            			$('ul.suggestions').show();
	                    	return;
	            		}	            	
		            }
            	}
            }
            
            
            if(!fullTextSearch && selectedCategoryIds.length > 0) 
            {            	
            	$('ul.suggestions').show();
            	return;
            }
            
            //prepare selected categories
            selectedCategoryIds = selectedCategoryIds.concat(selectedChildIds); 
            
            //if(0)
            //{
	        //    for(var searchItemIndex in searchItems)
	        //    {
	        //    	var searchItem = searchItems[searchItemIndex];
	        //    	var searchItemLowerCase = searchItem.toString().toLowerCase();
		    //        for(var itemIndex in this.channelCategories) 
		    //        {	            	
		    //        	var item = this.channelCategories[itemIndex];            		
		    //        	_.each(item.pathitems, function(pathItem, pathItemIndex){
		    //        		if(pathItem.toString().toLowerCase() == searchItemLowerCase) 
		    //	           	{
		    //        			self.channelCategoriesSearchItems.push(item);
		    //	           		if(self.channelCategoriesSearchItems().length >= maxItems)
		    //	                   	return;
		    //	           	}
		    //        	});
		    //        	if(self.channelCategoriesSearchItems().length >= maxItems)
		    //               	return;	    
		    //        	
		    //        }
	        //    }
            // }
            
            
            //select categories which contain given search word
            
            for(var searchItemIndex = 0; searchItemIndex < searchItemsFolded.length; searchItemIndex++)
            {  
            	var searchItem = searchItemsFolded[searchItemIndex];
	            for(var itemIndex in this.channelCategories) 
	            {
	            	var item = this.channelCategories[itemIndex];
	            	if(item && item.path && selectedCategoryIds.indexOf(item.id) < 0 && 
	            			item.path_folded.indexOf(searchItem) >= 0)		            	
	            	{
	            		this.channelCategoriesSearchItems.push(item);
	            		if(this.channelCategoriesSearchItems().length >= maxItems)
	            		{
	            			$('ul.suggestions').show();
	                    	return;
	            		}
	            	}
	            }
            }
            
            if( this.channelCategoriesSearchItems().length == 0) {
            	this.addNoSuggestionsText();
            } else {
            	$('ul.suggestions').show();
            }                        
         },
         
         addNoSuggestionsText: function() {
         	
         	this.channelCategoriesSearchItems.push( {
         		'id': 0,
         		'name' : 'No suggestions found',
         		'pathitems': []
         	});
         },
         
         preview: function ( locale) {     
         	
         	$('.modal-category-preview .modal-content').remove();
         	
         	var previewButtons = [];
         	
         	var tooltipHtml = self.previewHelpUrl ? mageTemplate( tooltipTemplate, { url: self.previewHelpUrl}) : '';
//	         	previewButtons.push({
//				    text: tooltipHtml,
//				    click: function () {
//				    }
//	         	});
         	
         	previewButtons.push({
                text: $.mage.__('Back'),
                class: 'back',
                click: function () {
                	this.closeModal();
                }
         	});
         	
             var modal = $('<div/>')
             modal.modal({
             	 type: 'slide',
                 title: $.mage.__('Category Mapping Preview') + tooltipHtml,
                 innerScroll: true,
                 modalClass: 'modal-category-preview',
                 buttons: previewButtons
             });                                                  
             
             this.saveData( true, function() {
            	 
	             $.ajax({
	             	method: 'get',
	         	    url: self.previewUrl,
	         	    data: {taxonomy_locale: locale},
	         	    showLoader: true, // enable loader
	         	 }).done(function( data ) {            		
	         		
	         		if( data.error) {
	         			alert( data.message);
	         		} else {
	         			modal.html( data);
	         		}            		
	     	     });             
             });
             
             modal.trigger('openModal');
         },
    });        
    
    function RuleItem(magentoCategoryIds, channelCategoryId, magentoCategories) 
    {
        var self = this;        
        self.magentoCategoryIds = ko.observable(magentoCategoryIds);
        self.magentoCategoryLabelsArray = ko.computed(function() {
        	var ids = self.magentoCategoryIds().replace(/ /g, "").split(",");
        	
        	var labelsArray = [];
        	
        	if(!self.magentoCategoryIds())
        		return labelsArray;
        	
        	for (var i = 0; i < ids.length; i++) 
        	{
        		var index = ids[i];
        		var path = "Unknown Category Path";
        		if(magentoCategories[index])
        			path = magentoCategories[index]['path'].replace(";amp&","&");
        		labelsArray.push(index + " - " + path);
        	}
        	return labelsArray;        	
            }, self);        
        
        self.channelCategoryId = ko.observable(channelCategoryId);                
    };
});

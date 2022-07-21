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
    'Magento_Ui/js/modal/alert'
], function (Component, $, ko, magAlert) {
    'use strict';
    
    var self;

    return Component.extend({
        
        defaults: {
            items: [],
            loadUrl: "",
            path: "Not adjusted yet",
            feedfile: "",
        },
        
        initObservable: function () {
           
            self = this;
            
            this._super().observe('path');
            
            this.items = ko.observableArray([]);
            
            $('#test_connection').click(function () {
                self.testFtpConnection();
            });
            
            $('#protocol').change(function () {
                self.changeFtpProtocol(this);
            });
            
            // init setup
            self.showFieldsByProtocol($('#protocol'));
            
            this.load();
            
            return this;
        },
        
        applyData: function ( data) {
            
            self.path(data.path);
            
            self.items.removeAll();
            
            for (var i = 0; i < data.list.length; i++) {
            data.list[i]['feedfile'] = ( data.list[i].name == this.feedfile);
                
                self.items.push(data.list[i]);
            }
            
            $('#ftp_client_table').show();
        },
        
        
        
        load: function ( index) {
                        
            var postData = this.getFormData();
            if ( postData['feed[ftp][hostname]'] == "" || postData['feed[ftp][username]'] == "" || postData['feed[ftp][password]'] == "") {
                return false;
            }
            
            if ( index != undefined) {
                var row = self.items()[ index];
                
                // file is downloaded
                if ( row.type == 'file') {
                    self.download(row);
                    return true;
                }
                
                if ( row.path) {
                    postData.path = row.path;
                } else {
                    postData.path = self.path() + '/' + row.name;
                }
            }
            
            $.ajax({
                method: 'get',
                url: self.loadUrl,
                data: postData,
                showLoader: true, // enable loader
                dataType: 'json'
            }).done(function ( data ) {
                if ( data.error) {
                    magAlert({title: "FTP Client Error",content: data.message});
                } else {
                    self.applyData(data);
                }
            }).fail(function ( jqXHR, textStatus, errorThrown) {
                magAlert({title: "FTP Client Error",content: errorThrown});
            });
        },
        download: function ( row) {
            
            var filename = self.path() + '/' + row.name;
            var url = self.loadUrl + '?file=' + filename;
            
            var win = window.open(url, '_blank');
            win.focus();
        },
        
        testFtpConnection: function () {
            
            var postData = this.getFormData();
            postData.test = true;
            
            $.ajax({
                method: 'post',
                url: self.loadUrl,
                data: postData,
                showLoader: true
            }).done(function ( data ) {
                if ( data.error) {
                    magAlert({
                        title: "FTP Connection Error",
                        content: data.message,
                    });
                } else {
                    magAlert({
                        title: "FTP Connection Test",
                        content: data.message,
                    });
                    
                    self.applyData(data);
                }
            }).fail(function ( jqXHR, textStatus, errorThrown) {
                magAlert({title: "FTP Client Error",content: errorThrown});
            });
        },
        
        changeFtpProtocol:  function ( select) {
            if ( $(select).val() == 'SFTP') {
                $("#port").val(22);
            } else {
                $("#port").val(21);
            }
            this.showFieldsByProtocol( select);
        },
        
        showFieldsByProtocol:  function ( select) {
            if ( $(select).val() == 'SFTP') {
                $('.field-passive_mode').hide();
                $('.field-ssl').hide();
            } else {
                $('.field-passive_mode').show();
                $('.field-ssl').show();
            }
        },
        
        getFormData: function () {
            
            return this.serializeObject($("[name^='feed[ftp]']"));
        },
        
        serializeObject: function ( form) {
            var paramObj = {};
            $.each(form.serializeArray(), function (_, kv) {
              if (paramObj.hasOwnProperty(kv.name)) {
                paramObj[kv.name] = $.makeArray(paramObj[kv.name]);
                paramObj[kv.name].push(kv.value);
              } else {
                paramObj[kv.name] = kv.value;
              }
            });
            
            return paramObj;
        }
        
    });
});




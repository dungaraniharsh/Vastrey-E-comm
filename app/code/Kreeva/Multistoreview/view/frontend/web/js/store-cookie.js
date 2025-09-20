/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'domReady',
    'consoleLogger',
    'jquery-ui-modules/widget',
    'mage/cookies'
], function ($, domReady, consoleLogger) {

    /**
     * FormKey Widget - this widget is generating from key, saves it to cookie and
     */
    $.widget('mage.storeCookie', {
        options: {
            storeID: null,
            storeCode: null
        },

        /**
         * Creates widget 'mage.formKey'
         * @private
         */
        _create: function () {
            var storeviewcode = $.mage.cookies.get('storeviewcode'),
                options = {
                    secure: window.cookiesConfig ? window.cookiesConfig.secure : false
                };

            if (this.options.storeCode != null) {
                $.mage.cookies.set('storeviewcode', this.options.storeCode, options);
            }
        }
    });
    

    domReady(function () {
        $('body')
            .storeCookie();
    });

    return {
        'storeCookie': $.mage.storeCookie
    };
});

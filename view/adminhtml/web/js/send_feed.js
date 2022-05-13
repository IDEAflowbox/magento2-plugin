define([
    'jquery',
    'Magento_Ui/js/modal/alert',
    'jquery/ui'
], function ($, alert) {
    'use strict';

    $.widget('cyberkonsultant.sendFeed', {
        options: {
            url: '',
            elementId: '',
            successText: '',
            failedText: ''
        },

        /**
         * Bind handlers to events
         */
        _create: function () {
            this._on({
                'click': $.proxy(this._sendFeed, this)
            });
        },

        /**
         * @private
         */
        _sendFeed: function () {
            var result = this.options.failedText,
                element =  $('#' + this.options.elementId),
                self = this,
                params = {
                    form_key: window.FORM_KEY,
                },
                msg = '';

            element.removeClass('success').addClass('fail');

            $.ajax({
                url: this.options.url,
                showLoader: true,
                data: params,
                headers: this.options.headers || {}
            }).done(function (response) {
                if (response['success']) {
                    element.removeClass('fail').addClass('success');
                    result = self.options.successText;
                } else {
                    msg = response.errorMessage;

                    if (msg) {
                        alert({
                            content: msg
                        });
                    }
                }
            }).always(function () {
                $('#' + self.options.elementId + '_result').text(result);
            });
        }
    });

    return $.cyberkonsultant.sendFeed;
});

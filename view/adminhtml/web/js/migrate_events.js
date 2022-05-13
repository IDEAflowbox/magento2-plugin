define([
    'jquery',
    'Magento_Ui/js/modal/alert',
    'jquery/ui'
], function ($, alert) {
    'use strict';

    $.widget('cyberkonsultant.migrateEvents', {
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
                'click': $.proxy(this._migrateEvents, this)
            });
        },

        /**
         * @private
         */
        _migrateEvents: function (event, page = 1) {
            var result = this.options.failedText,
                element =  $('#' + this.options.elementId),
                self = this,
                params = {
                    form_key: window.FORM_KEY,
                },
                msg = '';

            element.removeClass('success').addClass('fail');

            $.ajax({
                url: this.options.url + '?page=' + page,
                showLoader: true,
                data: params,
                headers: this.options.headers || {}
            }).done(function (response) {
                if (response['next_page']) {
                    self._migrateEvents(event, response['next_page']);
                    return;
                }

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

    return $.cyberkonsultant.migrateEvents;
});

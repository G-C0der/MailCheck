Ext.define('Desktop.util.StoreUtil', {

    /**
     * Constructor
     */
    constructor: function () {

        // Set utils
        this.messageUtil = new Desktop.util.MessageUtil;
    },

    /**
     * Ajax request
     * @param url
     * @param scope
     * @param method
     * @param params
     * @param callback
     * @param arguments
     */
    ajaxRequest: function (url, scope, method, params, callback) {
        var me = this;

        // Ajax request
        Ext.Ajax.request({
            headers: {
                'Content-Type': 'application/json'
            },

            url: url,
            scope: scope,
            method: method,
            async: false,
            params: Ext.encode(params),

            /**
             * Success
             * @param response
             * @param headers
             */
            success: function (response, headers) {
                var responseText = Ext.decode(response.responseText);
                console.log(responseText)
                if (responseText.success && callback)
                    callback(arguments);
                me.showResponseMessage(responseText);
            },

            /**
             * Failure
             * @param response
             * @param headers
             */
            failure: function (response, headers) {
                me.messageUtil.showInfoBox('Fehler', 'Status: ' + response.status + '<br>' +
                    'Status-Text: ' + response.statusText + '<br>' +
                    'Response-Text: ' + response.responseText)
            }
        });
    },

    /**
     * Submit form
     * @param url
     * @param scope
     * @param form
     * @param params
     * @param callback
     */
    submitForm: function (url, scope, form, params, callback) {
        var me = this;

        // Submit form
        form.submit({
            url: url,
            scope: scope,
            method: "POST",
            params: Ext.encode(params),
            clientValidation: true,

            /**
             * Success
             * @param owner
             * @param form
             */
            success: function (owner, form) {
                if (callback)
                    callback(scope);

                me.showResponseMessage(form);
            },

            /**
             * Failure
             * @param owner
             * @param form
             */
            failure: function (owner, form) {
                me.showResponseMessage(form);
            }
        });
    },

    /**
     * Show response message
     * @param responseText
     */
    showResponseMessage: function (responseText) {

        // If response message set as expected
        if (responseText.message && responseText.message.title && responseText.message.text)

            // Show response message in info box
            this.messageUtil.showInfoBox(responseText.message.title, responseText.message.text);
    },

    /**
     * Text field filter
     * @param scope
     * @param filterField
     */
    textFieldFilter: function (scope, filterField) {

        var view = scope.getView(),
            filters = view.getStore().getFilters(),
            property = filterField.filterProperty;
        if (!scope["textFieldFilters"])
            scope["textFieldFilters"] = {};

        if (filterField.value) {
            scope.textFieldFilters[filterField.itemId] = filters.add({
                property: typeof property === "object" ? property.main[property.sub] : property,
                value: filterField.value,
                anyMatch: true,
                caseSensitive: false
            });
        }
        else if (scope.textFieldFilters[filterField.itemId]) {
            filters.remove(scope.textFieldFilters[filterField.itemId]);
            scope.textFieldFilters[filterField.itemId] = null;
        }
    }
});
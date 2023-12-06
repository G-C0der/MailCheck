Ext.define('Desktop.controller.desktop.DesktopViewController', {
    extend: 'Ext.app.ViewController',
    alias: 'controller.DesktopViewController',

    /**
     * Laravel token
     */
    laravelToken: null,

    /**
     * Endpoint
     */
    endpoints: {
        logout: '/logout'
    },

    constructor: function (config) {
        this.laravelToken = window.Laravel.csrfToken;
        this.callParent([config]);
    },

    onItemSelected: function (sender, record) {
        Ext.Msg.confirm('Confirm', 'Are you sure?', 'onConfirm', this);
    },

    onConfirm: function (choice) {
        if (choice === 'yes') {
            //
        }
    },

    /**
     * Logout the user
     */
    logout: function () {
        Ext.Ajax.request({
            headers: {
                'Content-Type': 'application/json'
            },

            url: this.endpoints.logout,
            scope: this,
            method: 'POST',

            /**
             * Success
             * @param response
             * @param action
             */
            success: function (response, action) {
                location.reload();
            },

            /**
             * Failure
             * @param response
             * @param action
             */
            failure: function (response, action) {
                location.reload();
            }
        });
    }
});

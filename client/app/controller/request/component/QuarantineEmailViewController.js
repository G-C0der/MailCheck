Ext.define('Desktop.controller.request.component.QuarantineEmailViewController', {
    extend: 'Ext.app.ViewController',
    alias: 'controller.viewController-quarantineEmail',

    /**
     * Initialization
     * @param config
     */
    init: function (config) {

        // Parent initialization
        this.callParent([config]);

        // Set view
        this.view = this.getView();
    },

    
});

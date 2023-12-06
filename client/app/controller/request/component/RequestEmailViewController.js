Ext.define('Desktop.controller.request.component.RequestEmailViewController', {
    extend: 'Ext.app.ViewController',
    alias: 'controller.viewController-requestEmail',

    /**
     * Initialization
     * @param config
     */
    init: function (config) {

        // Parent initialization
        this.callParent([config]);

        // Set view
        this.view = this.getView();
    }
});

Ext.define('Desktop.controller.request.component.QuarantineEmailAttachmentsViewController', {
    extend: 'Ext.app.ViewController',
    alias: 'controller.viewController-quarantineEmailAttachments',

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

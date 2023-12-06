Ext.define('Desktop.controller.request.RequestEmailsViewController', {
    extend: 'Ext.app.ViewController',
    alias: 'controller.viewController-requestEmails',

    /**
     * Initialization
     * @param config
     */
    init: function (config) {

        // Parent initialization
        this.callParent([config]);

        // Set view
        this.view = this.getView();

        // Set utils
        this.viewUtil = new Desktop.util.ViewUtil;
        this.storeUtil = new Desktop.util.StoreUtil;
    },

    /**
     * An always pending task to refresh the requests grid every 60 seconds
     */
    startStoreRefreshTask: function () {
        var requestsStore = this.view.getStore();

        this.storeRefreshTask = Ext.TaskManager.start({
            run: function () {
                requestsStore.reload()
            },
            interval: 60000
        });
    },

    /**
     * Stop the store refresh task
     */
    stopStoreRefreshTask: function () {
        Ext.TaskManager.stop(this.storeRefreshTask);
    },

    /**
     * Show the details of the request
     * @param view
     * @param recordIndex
     * @param cellIndex
     * @param item
     * @param e
     * @param record
     */
    showDetails: function (view, recordIndex, cellIndex, item, e, record) {
        this.viewUtil.createWin("Details", "win-requestDetail", 1335, 900,
            "RequestDetailForm", record.getData(), "pictos pictos-news");
    },

    /**
     * Render status icon
     * @param value
     * @param meta
     * @param record
     * @param rowIndex
     * @param colIndex
     * @param store
     * @param view
     * @returns {string}
     */
    statusRenderer: function (value, meta, record, rowIndex, colIndex, store, view) {
        var buffer = "";
        if (Ext.isArray(value)) {
            Ext.Array.each(value, function(entry, index, list) {
                buffer += '<div class="grid-icon-cls ' + record.get("status_icon") + '"></div>';
            })
        } else {
            buffer += '<div class="grid-icon-cls ' + record.get("status_icon") + '"></div>';
        }
        return buffer;
    },

    /**
     * Text field store filter
     * @param filterField
     */
    requestsTextFilter: function (filterField) {
        this.storeUtil.textFieldFilter(this, filterField)
    }
});

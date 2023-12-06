Ext.define('Desktop.controller.request.RequestDetailViewController', {
    extend: 'Ext.app.ViewController',
    alias: 'controller.viewController-requestDetail',

    /**
     * Endpoints
     */
    endpoints: {

        // Forward the quarantine email to logged in user
        forwardQuarantineEmail: "/request/forward",

        // Release or stash quarantine email
        handleRequest: "/request/handle"
    },

    /**
     * Possible status list of the request
     */
    requestStatus: {
        pending: "pending",
        done: "done"
    },

    /**
     * Initialization
     * @param config
     */
    init: function (config) {

        // Parent initialization
        this.callParent([config]);

        // Set view
        this.view = this.getView();

        this.requestEmailsGrid = Ext.ComponentQuery.query("#grid-requestEmails")[0];

        // Set the request data
        console.log(this.getView().getViewModel())
        this.requestData = this.view.getViewModel().data;

        // Set utils
        this.storeUtil = new Desktop.util.StoreUtil;
        this.messageUtil = new Desktop.util.MessageUtil;

        // Initialize stores
        this.prepareStoreData();
        this.setStores();
        this.bindStores();
    },

    /**
     * Prepare data which will be used by the stores
     */
    prepareStoreData: function () {
        var me = this,
            requestEmailGridColumnsData = {},
            requestEmailGridColumns = [];

        // Request email data
        this.requestEmailData = [];

        // Get the column name and data index of the "Desktop.view.request.RequestEmailsGrid" grid
        Ext.iterate(this.requestEmailsGrid.columns, function (column) {
            requestEmailGridColumnsData[column.dataIndex] = column.text;
        });

        // Add each request data value to "this.requestEmailData" (as the "value" property) where the key is in
        // "requestEmailGridColumns"
        // In addition add the associated column name from "requestEmailGridColumnsData" (as the "name" property)
        requestEmailGridColumns = Object.keys(requestEmailGridColumnsData);
        Ext.iterate(this.requestData, function (key, value) {
            if (requestEmailGridColumns.includes(key))
                me.requestEmailData.push({
                    name: requestEmailGridColumnsData[key],
                    value: value
                });
        });

        // Quarantine email data
        this.quarantineEmailData = this.requestData["quarantine_email"];

        // Quarantine email attachments data
        this.quarantineEmailAttachmentsData = this.quarantineEmailData["attachments"];
    },

    /**
     * Create stores and fill them with the pre prepared data
     */
    setStores: function () {

        // Request email store
        this.requestEmailStore = Ext.create("Desktop.store.request.component.RequestEmailStore", {
            data: this.requestEmailData
        });

        // Quarantine email store
        this.quarantineEmailStore = Ext.create("Desktop.store.request.component.QuarantineEmailStore");
        this.quarantineEmailStore.add(this.quarantineEmailData);

        // Quarantine email attachments store
        this.quarantineEmailAttachmentsStore =
            Ext.create("Desktop.store.request.component.QuarantineEmailAttachmentsStore", {
                data: this.quarantineEmailAttachmentsData
            });
    },

    /**
     * Bind the stores to the desired view / component
     */
    bindStores: function () {
        var requestEmailGrid = this.view.down("#grid-requestEmail"),
            quarantineEmailForm = this.view.down("#form-quarantineEmail"),
            quarantineEmailAttachmentsGrid = this.view.down("#grid-quarantineEmailAttachments");

        // Bind request email store
        requestEmailGrid.setStore(this.requestEmailStore);

        // Bind quarantine email store
        quarantineEmailForm.getForm().setValues(this.quarantineEmailStore.getAt(0).data);

        // Bind quarantine email attachments store
        quarantineEmailAttachmentsGrid.setStore(this.quarantineEmailAttachmentsStore);
    },

    /**
     * Hide release and stash button of status is "done"
     */
    buttonVisibilityHandling: function () {
        if (this.requestData["status_name"] === "done") {
            this.view.down("#button-release").hide();
            this.view.down("#button-stash").hide();
        }
    },

    /**
     * Forward the quarantine email to logged in user
     */
    forwardQuarantineEmail: function () {

        this.storeUtil.ajaxRequest(this.endpoints.forwardQuarantineEmail, this, "POST", {

            // Amavis identifier to locate the quarantine email on the corresponding Amavis server
            amavisIdentifier: this.requestData["amavis_identifier"]
        }, function () {
            this.view.up().close();
        });
    },

    /**
     * Confirm release request handling
     * Amavis identifier has to be inserted and then the confirm button has to be pressed in order to release / stash
     * the quarantine email
     * @param button
     */
    confirmRequestHandling: function (button) {

        // If true, the quarantine email will be released
        // If false, the quarantine email stays in quarantine
        this.releaseQuarantineEmail = button.itemId === "button-release";

        // Set the popup box attributes
        var title = this.releaseQuarantineEmail ? "Freigabe" : "Ablehnung",
            text = this.releaseQuarantineEmail ? "durchzuführen" : "abzulehnen";
        text = `Um die Freigabe ${text}, geben sie bitte den Isolations-Pfad ein:`;

        // Open popup box
        this.messageUtil.showPopupInputBox(title, text, this, this.validateRequestHandling);
    },

    /**
     * Validate the release request
     * @param choice
     * @param input
     */
    validateRequestHandling: function (choice, input) {

        // Abort if canceled on popup box
        if (choice === "cancel")
            return;

        // Abort if input not Amavis identifier of current record and notify the user
        if (!this.isCurrentAmavisIdenitifer(input)) {
            this.messageUtil.showInfoBox("Fehler", "Falscher Isolations-Pfad eingegeben.");
            return;
        }

        // If release quarantine email, handle the request
        if (this.releaseQuarantineEmail)
            return this.handle();

        // If stash quarantine email, open optional reason input box and then handle the request
        this.messageUtil.showPopupInputBox("Grund (optional)", "Hier können Sie den Grund für die " +
            "Ablehnung angeben:", this, this.handle, true, Ext.MessageBox.OK, {
            ok: "Bestätigen"
        });
    },

    /**
     * Handle the release request
     * @param choice
     * @param stashReason
     */
    handle: function (choice, stashReason) {

        // Scope variable for closure function
        var me = this,

            // Parameters for the post request
            params = {

                // The Amavis identifier of the request which will be handled
                amavisIdentifier: this.requestData["amavis_identifier"],

                // Determines if the quarantine email gets released or stashed
                release: this.releaseQuarantineEmail,

                // Reason the quarantine email gets stashed
                reason: stashReason ?? null
            };

        // Request to the backend
        this.storeUtil.ajaxRequest(this.endpoints.handleRequest, this,
            "POST", params, function () {

                // On success, reload the store of the main grid "Desktop.view.request.RequestEmailsGrid"
                me.requestEmailsGrid.getStore().reload();
                me.view.up().close();
            });
    },

    /**
     * Check if value is the Amavis identifier of the current record
     * @param value
     */
    isCurrentAmavisIdenitifer: function (value) {
        return value === this.requestData["amavis_identifier"];
    }
});

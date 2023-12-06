Ext.define("Desktop.controller.request.RequestController", {
    extend: 'Ext.app.Controller',
    alias: 'controller.controller-request',

    requires: [
        'Desktop.controller.request.RequestEmailsViewController',
        'Desktop.controller.request.RequestDetailViewController',
        'Desktop.controller.request.component.QuarantineEmailViewController',
        'Desktop.controller.request.component.QuarantineEmailAttachmentsViewController',
        'Desktop.controller.request.component.RequestEmailViewController'
    ],

    views: [
        "Desktop.view.request.RequestEmailsGrid",
        "Desktop.view.request.RequestDetailForm",
        "Desktop.view.request.component.QuarantineEmailForm",
        "Desktop.view.request.component.QuarantineEmailAttachmentsGrid",
        "Desktop.view.request.component.RequestEmailGrid"
    ],

    stores: [
        "Desktop.store.request.RequestsStore",
        "Desktop.store.request.component.QuarantineEmailStore",
        "Desktop.store.request.component.QuarantineEmailAttachmentsStore",
        "Desktop.store.request.component.RequestEmailStore"
    ],

    models: [
        "Desktop.model.request.RequestsModel",
        "Desktop.model.request.component.QuarantineEmailModel",
        "Desktop.model.request.component.QuarantineEmailAttachmentsModel",
        "Desktop.model.request.component.RequestEmailModel"
    ]
});
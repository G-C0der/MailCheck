Ext.define("Desktop.view.request.component.QuarantineEmailAttachmentsGrid", {
    extend: "Ext.grid.Panel",
    requires: [
        "Ext.grid.*"
    ],

    xtype: "QuarantineEmailAttachmentsGrid",
    itemId: "grid-quarantineEmailAttachments",
    controller: "viewController-quarantineEmailAttachments",
    overflowY: 'auto',
    autoLoad: true,
    autoScroll: true,

    columns: [
        {
            dataIndex: "name",
            text: "Name",
            flex: 1
        },
        {
            dataIndex: "type",
            text: "Typ",
            flex: 1
        },
        {
            dataIndex: "size",
            text: "Grösse",
            width: 80,
            minWidth: 80,
            maxWidth: 80
        }
    ]
});
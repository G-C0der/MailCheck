Ext.define("Desktop.view.request.RequestEmailsGrid", {
    extend: "Ext.grid.Panel",
    requires: [
        "Ext.grid.*"
    ],

    xtype: "RequestEmailsGrid",
    itemId: "grid-requestEmails",
    controller: "viewController-requestEmails",
    overflowY: "auto",
    autoLoad: true,
    autoScroll: true,

    store: Ext.create("Desktop.store.request.RequestsStore"),

    selModel: {
        type: "spreadsheet",
        rowSelect: false
    },

    plugins: {
        ptype: "clipboard"
    },

    listeners: {
        afterRender: "startStoreRefreshTask",
        destroy: "stopStoreRefreshTask"
    },

    dockedItems: [
        {
            xtype: "toolbar",
            dock: "top",
            items: [
                {
                    xtype: "textfield",
                    margin: "0 0 0 0",
                    width: 90,
                    emptyText: "Anfrage-ID",
                    enableKeyEvents: true,
                    itemId: "pkIdFilter",
                    filterProperty: "pk_id",
                    listeners: {
                        keyUp: "requestsTextFilter",
                        buffer: 500
                    }
                },
                {
                    xtype: "textfield",
                    margin: "0 0 0 10",
                    width: 180,
                    emptyText: "Isolations Pfad",
                    enableKeyEvents: true,
                    itemId: "amavisIdentifier",
                    filterProperty: "amavis_identifier",
                    listeners: {
                        keyUp: "requestsTextFilter",
                        buffer: 500
                    }
                },
                {
                    xtype: "textfield",
                    margin: "0 0 0 10",
                    width: 220,
                    emptyText: "Anfrager E-Mail",
                    enableKeyEvents: true,
                    itemId: "requesterEmail",
                    filterProperty: "sender_email",
                    listeners: {
                        keyUp: "requestsTextFilter",
                        buffer: 500
                    }
                },
                {
                    xtype: "textfield",
                    margin: "0 0 0 10",
                    width: 200,
                    emptyText: "Anfrager Name",
                    enableKeyEvents: true,
                    itemId: "requesterName",
                    filterProperty: "sender_name",
                    listeners: {
                        keyUp: "requestsTextFilter",
                        buffer: 500
                    }
                },
                {
                    xtype: "textfield",
                    margin: "0 0 0 10",
                    width: 220,
                    emptyText: "Quar. E-M. Absender E-Mail",
                    enableKeyEvents: true,
                    itemId: "senderEmail",
                    filterProperty: "quarantine_email.sender_email",
                    listeners: {
                        keyUp: "requestsTextFilter",
                        buffer: 500
                    }
                },
                {
                    xtype: "textfield",
                    margin: "0 0 0 10",
                    width: 200,
                    emptyText: "Quar. E-M. Absender Name",
                    enableKeyEvents: true,
                    itemId: "senderName",
                    filterProperty: "quarantine_email.sender_name",
                    listeners: {
                        keyUp: "requestsTextFilter",
                        buffer: 500
                    }
                }
            ]
        }
    ],

    columns: [
        {
            dataIndex: "pk_id",
            text: "Anfrage-ID",
            width: 90,
            minWidth: 90,
        },
        {
            dataIndex: "amavis_identifier",
            itemId: "column-amavisIdentifier",
            text: "Isolations Pfad",
            width: 180,
            minWidth: 180,
            maxWidth: 180
        },
        {
            text: "Anfrager",
            columns: [
                {
                    dataIndex: "sender_email",
                    text: "E-Mail",
                    width: 300,
                    minWidth: 100
                },
                {
                    dataIndex: "sender_name",
                    text: "Name",
                    width: 250,
                    minWidth: 100
                }
            ]
        },
        {
            dataIndex: "message",
            text: "Nachricht",
            flex: 1,
            minWidth: 100
        },
        {
            xtype: "datecolumn",
            dataIndex: "timestamp",
            text: "Eingetroffen",
            format: "d.m.Y H:i",
            width: 140,
            minWidth: 140
        },
        {
            dataIndex: "status",
            text: "Status",
            width: 80,
            minWidth: 80,
            maxWidth: 80,
            align: "center",
            renderer: "statusRenderer"
        },
        {
            xtype: "actioncolumn",
            sortable: false,
            menuDisabled: true,
            align: "center",
            width: 80,
            minWidth: 80,
            maxWidth: 80,
            items: [
                {
                    iconCls: "pictos pictos-news",
                    tooltip: "Details",
                    handler: "showDetails"
                }
            ]
        }
    ]
});
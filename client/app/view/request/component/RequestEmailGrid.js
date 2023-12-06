Ext.define("Desktop.view.request.component.RequestEmailGrid", {
    extend: "Ext.grid.Panel",
    requires: [
        "Ext.grid.*"
    ],

    xtype: "RequestEmailGrid",
    itemId: "grid-requestEmail",
    controller: "viewController-requestEmail",
    overflowY: 'auto',
    autoLoad: true,

    selModel: {
        type: "spreadsheet",
        rowSelect: false
    },

    plugins: {
        ptype: "clipboard"
    },

    columns: [
        {
            dataIndex: "name",
            text: "Bezeichnung",
            width: 120,
            minWidth: 50,
            maxWidth: 120
        },
        {
            dataIndex: "value",
            text: "Wert",
            flex: 1
        }
    ]
});
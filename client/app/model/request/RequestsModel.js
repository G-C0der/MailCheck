Ext.define("Desktop.model.request.RequestsModel", {
    extend: "Ext.data.Model",

    fields: [
        {
            type: "int",
            name: "pk_id"
        },
        {
            type: "string",
            name: "amavis_identifier"
        },
        {
            type: "string",
            name: "sender_email"
        },
        {
            type: "string",
            name: "sender_name"
        },
        {
            type: "string",
            name: "message"
        },
        {
            type: "date",
            dateFormat: "Y-m-d H:i:s",
            name: "timestamp"
        },
        {
            type: "int",
            name: "status"
        },
        {
            type: "string",
            name: "status_name"
        },
        {
            type: "string",
            name: "status_icon"
        },
        {
            type: "string",
            name: "status_tooltip"
        },
        {
            type: "auto",
            name: "quarantineEmail"
        }
    ]
});
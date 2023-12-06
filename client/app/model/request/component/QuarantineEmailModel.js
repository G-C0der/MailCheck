Ext.define("Desktop.model.request.component.QuarantineEmailModel", {
    extend: "Ext.data.Model",

    fields: [
        {
            type: "int",
            name: "pk_id"
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
            name: "subject"
        },
        {
            type: "string",
            name: "message"
        },
        {
            type: "auto",
            name: "attachments"
        }
    ]
});
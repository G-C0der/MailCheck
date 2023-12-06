Ext.define("Desktop.model.request.component.QuarantineEmailAttachmentsModel", {
    extend: "Ext.data.Model",

    fields: [
        {
            type: "int",
            name: "pk_id"
        },
        {
            type: "string",
            name: "name"
        },
        {
            type: "string",
            name: "type"
        },
        {
            type: "string",
            name: "size"
        },
        {
            type: "auto",
            name: "attachments"
        }
    ]
});
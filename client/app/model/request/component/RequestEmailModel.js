Ext.define("Desktop.model.request.component.RequestEmailModel", {
    extend: "Ext.data.Model",

    fields: [
        {
            type: "string",
            name: "name"
        },
        {
            type: "auto",
            name: "value"
        }
    ]
});
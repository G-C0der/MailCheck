Ext.define("Desktop.store.request.RequestsStore", {
    extend: "Ext.data.Store",
    model: "Desktop.model.request.RequestsModel",

    proxy: {
        type: "ajax",
        url: "/request/getall",
        reader: {
            type: "json",
            rootProperty: "data",
            implicitIncludes: false
        }
    }
});
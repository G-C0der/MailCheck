Ext.define("Desktop.view.request.RequestDetailForm", {
    extend: "Ext.form.Panel",
    requires: [
        "Ext.form.*"
    ],

    xtype: "RequestDetailForm",
    itemId: "form-requestDetail",
    controller: "viewController-requestDetail",

    listeners: {
        afterRender: "buttonVisibilityHandling"
    },

    items: [
        {
            xtype: "fieldset",
            height: 900,
            width: 1350,
            layout: "vbox",
            border: false,
            items: [
                {
                    xtype: "QuarantineEmailForm",
                    margin: "-12 5 5 0",
                    height: 500,
                    width: 1300
                },
                {
                    xtype: "fieldset",
                    height: 400,
                    width: 1350,
                    layout: "hbox",
                    margin: "-35 5 5 -15",
                    border: false,
                    items: [
                        {
                            xtype: "fieldset",
                            title: "Anhänge",
                            margin: "5 5 0 0",
                            height: 320,
                            width: 840,
                            items: [
                                {
                                    xtype: "QuarantineEmailAttachmentsGrid",
                                    height: 310,
                                    width: 800
                                },
                            ]
                        },
                        {
                            xtype: "fieldset",
                            title: "Freigabeanfrage E-Mail",
                            margin: "5 5 0 0",
                            height: 320,
                            width: 456,
                            items: [
                                {
                                    xtype: "RequestEmailGrid",
                                    height: 310,
                                    width: 420
                                }
                            ]
                        }
                    ]
                }
            ]
        }
    ],

    buttons: [
        {
            iconCls: "pictos pictos-action",
            text: "Weiterleiten",
            tooltip: "An die E-Mail Adresse des eingeloggten benutzers weiterleiten",
            handler: "forwardQuarantineEmail"
        },
        {
            xtype: "tbfill"
        },
        {
            itemId: "button-release",
            iconCls: "pictos pictos-check2",
            text: "Freigeben",
            handler: "confirmRequestHandling"
        },
        {
            itemId: "button-stash",
            iconCls: "pictos pictos-minus2",
            text: "Ablehnen",
            handler: "confirmRequestHandling"
        }
    ]
});
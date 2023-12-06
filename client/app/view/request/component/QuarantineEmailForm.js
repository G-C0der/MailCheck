Ext.define("Desktop.view.request.component.QuarantineEmailForm", {
    extend: "Ext.form.Panel",
    requires: [
        "Ext.form.*"
    ],

    xtype: "QuarantineEmailForm",
    itemId: "form-quarantineEmail",
    controller: "viewController-quarantineEmail",

    items: [
        {
            xtype: "fieldset",
            title: "Quarantänen E-Mail",
            padding: "5 5 5 5",
            layout: "vbox",
            items: [
                {
                    xtype: "fieldset",
                    title: "Absender",
                    layout: "hbox",
                    width: 1350,
                    border: false,
                    items: [
                        {
                            xtype: "textfield",
                            fieldLabel: "E-Mail",
                            name: "sender_email",
                            width: 600,
                            readOnly: true
                        },
                        {
                            xtype: "textfield",
                            fieldLabel: "Name",
                            name: "sender_name",
                            margin: "0 0 0 100",
                            width: 500,
                            readOnly: true,
                        }
                    ]
                },
                {
                    xtype: "fieldset",
                    title: "Inhalt",
                    layout: "vbox",
                    width: 1350,
                    border: false,
                    items: [
                        {
                            xtype: "textfield",
                            fieldLabel: "Betreff",
                            name: "subject",
                            width: 1250,
                            readOnly: true
                        },
                        {
                            xtype: "textarea",
                            fieldLabel: "Nachricht",
                            name: "message",
                            height: 220,
                            width: 1250,
                            readOnly: true,
                            scrollable: "vertical"
                        }
                    ]
                },
            ]
        }
    ]
});
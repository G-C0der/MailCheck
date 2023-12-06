/**
 * Application main view
 */
Ext.define('Desktop.view.desktop.DesktopTabPanel', {
    extend: 'Ext.tab.Panel',
    requires: [
        'Ext.layout.Fit'
    ],

    xtype: 'DesktopTabPanel',
    controller: 'DesktopViewController',
    itemId: "tabPanel-desktop",
    viewModel: 'DesktopViewModel',

    defaults: {
        tab: {
            iconAlign: 'top'
        }
    },

    tabBarPosition: 'bottom',

    items: [
        {
            title: 'Freigabeanfragen',
            iconCls: 'x-fa fa-mail-bulk',
            items: [
                {
                    xtype: "RequestEmailsGrid",
                    height: 840
                }
            ]
        }
    ],

    buttons: [
        {
            text: "Logout",
            handler: "logout"
        }
    ]
});

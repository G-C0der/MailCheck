Ext.define('Desktop.util.component.Notification', {
    extend: 'Ext.window.Toast',
    xtype: 'winToast',
    tpl: '<span>{message}</span><br><span>{data}</span>',
    align: 'tr',
    width: 350,

    config: {
        message: '',
        title: '',
        someData: []
    },

    /**
     * Initialization
     */
    initComponent: function() {
        var me = this;

        me.callParent();

        me.setData({
            message: me.getMessage(),
            data: me.getSomeData()
        });
    }
});
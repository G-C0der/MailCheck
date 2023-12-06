Ext.define('Desktop.view.desktop.component.PersonnelGrid', {
    extend: 'Ext.grid.Panel',
    xtype: 'PersonnelGrid',

    requires: [
        'Desktop.store.Personnel'
    ],

    title: 'Personnel',

    store: {
        type: 'personnel'
    },

    columns: [{
        text: 'Name',
        dataIndex: 'name',
        width: 100,
        cell: {
            userCls: 'bold'
        }
    }, {
        text: 'Email',
        dataIndex: 'email',
        width: 230
    }, {
        text: 'Phone',
        dataIndex: 'phone',
        width: 150
    }],

    listeners: {
        select: 'onItemSelected'
    }
});
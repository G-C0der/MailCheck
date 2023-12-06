Ext.define('Desktop.util.ViewUtil', {

    /**
     * Create window
     * @param title
     * @param winId
     * @param width
     * @param height
     * @param xtype
     * @param data
     * @param icon
     * @param callback
     * @param params
     */
    createWin: function (title, winId, width, height, xtype, data, icon, callback, params) {

        // Get the desktop app through the desktop global
        var desktop = MyDekstop.getDesktop(),

            // Get the window
            win = desktop.getWindow(winId);

        // If window non existent
        if(!win)

            // Create the window
            win = desktop.createWindow({
                title: title,
                winId: winId,
                width: width,
                minWidth: width,
                height: height,
                minHeight: height,
                maximizable: false,
                minimizable: false,
                resizable: false,
                icons: icon,
                layout: 'fit',
                autoShow: true,
                session: true,
                items:[
                    {
                        xtype: xtype,
                        viewModel: {
                            data: data
                        }
                    }
                ]
            });

        else

            // Show the window
            desktop.restoreWindow(win);

        // Callback
        if (callback)
            callback(params);

        // Return the window
        return win;
    },

    /**
     * Set form field values
     * @param view
     */
    setFormFieldValues: function (view) {

        // Get view model from view
        var viewModel = view.getViewModel(),
            record;

        // If view model and nested data property defined
        if (viewModel && viewModel.data) {

            // Set view model data on form
            record = viewModel.data;
            view.getForm().setValues(record);
        }
    }
});
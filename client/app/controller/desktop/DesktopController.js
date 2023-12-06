Ext.define("Desktop.controller.desktop.DesktopController", {
    extend: 'Ext.app.Controller',
    alias: 'controller.controller-desktop',

    requires: [
        'Desktop.controller.desktop.DesktopViewController'
    ],

    views: [
        'Desktop.view.desktop.DesktopTabPanel',
        'Desktop.view.desktop.component.PersonnelGrid'
    ],

    /**
     * Laravel token
     */
    laravelToken: null,

    /**
     * Constructor
     * @param config
     */
    constructor: function (config) {
        this.callParent([config]);

        var desktopConfig;

        // Set default ajax headers
        this.laravelToken = window.Laravel.csrfToken;
        Ext.Ajax.setDefaultHeaders({
            'X-CSRF-TOKEN': this.laravelToken
        });

        // Kill the loader
        var loader = Ext.get('loader-wrapper');
        if (loader)
            loader.destroy();

        // DesktopController global
        window.MyDekstop = this;

        // Set desktop
        desktopConfig = this.getDesktopConfig();
        this.desktop = new Ext.ux.desktop.Desktop(desktopConfig);
    },

    /**
     * This method returns the configuration object for the Desktop object. A derived
     * class can override this method, call the base version to build the config and
     * then modify the returned object before returning it.
     */
    getDesktopConfig: function() {
        var cfg = {
            app: this
        };

        Ext.apply(cfg, this.desktopConfig);

        return cfg;
    },

    /**
     * Get desktop
     * @returns {Ext.ux.desktop.Desktop}
     */
    getDesktop: function () {
        return this.desktop;
    }
});
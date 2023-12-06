Ext.define('Desktop.util.MessageUtil', {

    /**
     * Informational notification with a text message.info Box
     * @param {String} [title] Title to be displayed.
     * @param {String} [msg] Message to be displayed.
     */
    showInfoBox: function(title, msg) {
        var note;
        if (title === 'Error'){
            msg = "<span style='color:red;'>"+msg+"</span>";
            title = "<span style='color:red;'>"+title+"</span>";
        }
        title = title ? title : 'Notification';

        var config = Ext.apply({
            title: title,
            cls: 'ux-notification-light',
            iconCls: 'pictos pictos-warning_black',
            message: msg,
            closable: true
        });

        note = Ext.create('Desktop.util.component.Notification', config);
        note.show();
    },

    /**
     * Popup input box prompt
     * @param title
     * @param message
     * @param scope
     * @param multiline
     * @param callback
     * @param buttons
     * @param buttonText
     * @param prompt
     */
    showPopupInputBox: function (title, message, scope, callback, multiline = false,
                                 buttons = Ext.MessageBox.OKCANCEL, buttonText = {
            ok: "Bestätigen",
            cancel: "Abbrechen"
        }, prompt = true) {
        Ext.Msg.show({
            title: title,
            message: message,
            scope: scope,
            prompt: prompt,
            multiline: multiline,
            defaultFocus: 'textfield',
            buttons: buttons,
            buttonText: buttonText,
            fn: callback
        });
    },
});
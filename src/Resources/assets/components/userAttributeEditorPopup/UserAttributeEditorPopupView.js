var UserAttributeEditorPopupView = PopupView.extend({
    title: function() {
        return app.t('user_attribute_editor_popup.title');
    },

    buttons: function() {
        return [
            {
                class: 'btn-primary',
                title: app.t('user_attribute_editor_popup.save')
            }
        ]
    },

    init: function() {
        this.setBody(app.render('UserAttributeEditorPopup', {

        }));
    }
});
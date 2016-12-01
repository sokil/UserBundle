var UserAttributeEditorPopupView = PopupView.extend({
    title: function() {
        return app.t('user_attribute_editor_popup.title');
    },

    buttons: function() {
        return [
            {
                class: 'btn-primary',
                title: app.t('user_attribute_editor_popup.button.save'),
                attributes: {
                    "data-save": true
                }
            }
        ]
    },

    events: {
        'click [data-save]': function() {
            alert('Save');
        }
    },

    init: function() {
        this.setBody(app.render('UserAttributeEditorPopup', {
            attribute: this.model.toJSON(),
            availableTypes: this.model.availableTypes
        }));
    }
});
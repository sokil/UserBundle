var UserAttributeEditorPopupView = PopupView.extend({
    title: function() {
        var title = app.t('user_attribute_editor_popup.title');

        if (!this.model.isNew()) {
            var type = this.model.get('type');
            title += " (" + this.model.availableTypes[type]['label'] + ")";
        }

        return title;
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
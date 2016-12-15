var UserAttributeEditorPopupView = PopupView.extend({
    /**
     * List of events
     */
    events: {
        'click [data-save]': function() {
            this.save();
        }
    },

    init: function() {
        // set body
        this.setBody(app.render('UserAttributeEditorPopup', {
            attribute: this.model.toJSON(),
            availableTypes: this.model.availableTypes
        }));

        // on save close popup
        this.listenTo(this.model, 'sync', function() {
            this.remove();
        });
    },

    /**
     * Define popup title
     * @returns {string}
     */
    title: function() {
        var title = app.t('user_attribute_editor_popup.title');

        if (!this.model.isNew()) {
            var type = this.model.get('type');
            title += " (" + this.model.availableTypes[type]['label'] + ")";
        }

        return title;
    },

    /**
     * List of buttons
     * @returns {array}
     */
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

    /**
     * Save model
     */
    save: function() {
        var data = UrlMutator.unserializeQuery(this.$('form').serialize());
        this.model.save(data, {parse: false});
    }
});
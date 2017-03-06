var UserAttributeEditorPopupView = PopupView.extend({
    /**
     * List of events
     */
    events: {
        'click [data-save]': function() {
            this.save();
        }
    },

    init: function(options) {
        // Define attribute model.
        // It must be already fetched.
        if (!this.model) {
            this.model = new UserAttribute;
            this.model.set('type', options.attributeType);
        }

        // Add handler on save
        this.listenTo(this.model, 'sync', function() {
            // hide popup
            this.remove();

            // trigger event on save
            if ("function" === typeof options.onSave) {
                options.onSave.call(this, this.model);
            }
        });

        // create schema model
        var schemaModel = new UserAttributeSchema();

        // show popup on schema model sync
        this.listenTo(schemaModel, 'sync', function() {
            // set body
            this.setBody(app.render('UserAttributeEditorPopup', {
                schema: schemaModel.toJSON(),
                attribute: this.model.toJSON()
            }));
        });

        // sync model
        schemaModel.fetch({
            data: {
                type: this.model.get('type')
            }
        });
    },

    /**
     * Define popup title
     * @returns {string}
     */
    title: function() {
        var title = app.t('user_attribute_editor_popup.title');

        if (this.model.collection) {
            var type = this.model.get('type');
            title += " (" + this.model.collection.availableTypes[type]['label'] + ")";
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
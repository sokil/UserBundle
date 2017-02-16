var UserAttributesPageView = Marionette.LayoutView.extend({
    collection: null,

    regions: {
        'list': '.list'
    },

    events: {
        'click [data-new-attribute]': 'newAttributeClickListener'
    },

    initialize: function() {
        // init collection
        this.collection = app.container.get('userAttributeCollection');
        this.listenTo(this.collection, 'change update', this.renderAsync);

        // fetch collection
        this.collection.fetch({
            data: {
                formElements: 1 // show form input parameters
            }
        });
    },

    render: function() {

    },

    /**
     * Render page
     */
    renderAsync: function(){
        // render page
        this.$el.html(app.render('UserAttributesPage', {
            availableTypes: this.collection.availableTypes
        }));

        // render list
        this.list.show(new UsersAttributesListView({
            collection: this.collection
        }));
    },

    newAttributeClickListener: function(e) {
        e.preventDefault();
        var $button = $(e.currentTarget);
        var attributeType = $button.data('new-attribute');

        // create model
        var model = this.collection.add(
            {},
            {silent: true}
        );

        // show popup on model sync
        this.listenTo(model, 'syncDefaults', function() {
            app.popup(new UserAttributeEditorPopupView({
                model: model
            }));
        });

        // sync model
        model.fetch({
            data: {
                formElements: 1,
                type: attributeType
            }
        });
    }
});
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
        this.collection.fetch();
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

        app.popup(new UserAttributeEditorPopupView({
            attributeType: attributeType,
            onSave: function(model) {
                this.collection.add(model);
            }
        }));
    }
});
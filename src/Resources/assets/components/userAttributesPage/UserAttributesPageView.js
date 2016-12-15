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

        // fetch collection
        this.listenTo(this.collection, 'change update', this.renderAsync);
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

    newAttributeClickListener: function() {
        var model = this.collection.add({});
        app.popup(new UserAttributeEditorPopupView({
            model: model
        }));
    }
});
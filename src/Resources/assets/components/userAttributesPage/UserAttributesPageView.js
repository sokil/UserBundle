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

        // render page on collection sync
        this.listenToOnce(this.collection, 'sync', this.renderPage);

        // fetch collection
        this.collection.fetch();
    },

    render: function() {

    },

    renderPage: function() {
        // show container
        this.$el.html(app.render('UserAttributesPage', {
            availableTypes: this.collection.availableTypes
        }));

        // fill list
        this.renderList();

        // update list on collection change
        this.listenTo(this.collection, 'update', this.renderList);
    },

    /**
     * Render page
     */
    renderList: function(){
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
            }.bind(this)
        }));
    }
});
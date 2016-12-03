var UserAttributesPageView = Marionette.LayoutView.extend({
    regions: {
        'list': '.list'
    },

    events: {
        'click [data-new-attribute]': 'newAttributeClickListener'
    },

    initialize: function() {

    },

    render: function(){
        this.$el.html(app.render('UserAttributesPage'));
        this.list.show(new UsersAttributesListView({
            collection: app.container.get('userAttributeCollection')
        }));
    },

    newAttributeClickListener: function() {
        var model = app.container.get('userAttributeCollection').add({});
        app.popup(new UserAttributeEditorPopupView({
            model: model
        }));
    }
});
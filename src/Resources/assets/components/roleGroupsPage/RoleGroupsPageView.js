var RoleGroupsPageView = Marionette.LayoutView.extend({

    regions: {
        'list': '.list'
    },

    events: {
        'click [data-new-group]': 'newGroupClickEventListener'
    },

    initialize: function() {

    },

    render: function(){
        this.$el.html(app.render('RoleGroupsPage'));
        this.list.show(new RoleGroupsListView({
            collection: app.container.get('roleGroupsCollection')
        }));
    },

    newGroupClickEventListener: function() {
        var model = app.container.get('roleGroupsCollection').add({});
        app.popup(new RoleGroupEditorPopupView({
            model: model
        }));
    }
});
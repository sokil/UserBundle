var UsersPageView = Marionette.LayoutView.extend({
    
    regions: {
        list: '#users-list'
    },
    
    render: function() {
        // render page
        this.$el.html(app.render('UsersPage'));

        // render list view
        this.list.show(new UserListView({
            collection: new UserCollection()
        }));
    }
});
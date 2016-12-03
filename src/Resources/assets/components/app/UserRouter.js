var UserRouter = Marionette.AppRouter.extend({

    routes: {
        "users": "usersAction",
        "users/new": "editUserAction",
        "users/:id/edit": "editUserAction",
        "users/:id": "userAction",
        "roleGroups": "roleGroupsAction",
        "usersAttributes": "usersAttributesAction"
    },

    /**
     * Users list
     */
    usersAction: function() {
        app.rootView.content.show(new UsersPageView());
    },

    /**
     * User editor
     */
    editUserAction: function(id) {
        // model
        var collection = new UserCollection();
        var model = collection.add({});

        if (id && id !== 'new') {
            model.set('id', id);
        }

        // show editor
        app.rootView.content.show(new UserEditorView({
            model: model
        }));
    },

    /**
     * Show user
     */
    userAction: function(id) {
        // model
        var collection = new UserCollection();
        var model = collection.add({id: id});

        // render page
        app.rootView.content.show(new UserView({
            model: model
        }));
    },

    roleGroupsAction: function() {
        app.rootView.content.show(new RoleGroupsPageView());
    },

    usersAttributesAction: function() {
        app.rootView.content.show(new UserAttributesPageView());
    }
});
var UserEditorView = Marionette.LayoutView.extend({

    regions: {
        content: '.content'
    },

    initialize: function() {
        // new user editor
        this.listenTo(
            this.model,
            'syncDefaults',
            function() {
                this.content.show(new UserParametersView({
                    model: this.model
                }));
            }
        );

        // existed user editor
        this.listenTo(
            this.model,
            'sync',
            function() {
                // current user not allowed to edit this user
                if (!this.model.hasPermission('edit')) {
                    app.router.navigate('users/' + this.model.get('id'), {trigger: true});
                    return;
                }

                // show editor
                this.content.show(new TabPageView({
                    tabs: {
                        parameters: {
                            caption: app.t('Parameters'),
                            view: UserParametersView,
                            model: this.model
                        },
                        roles: {
                            caption: app.t('Roles'),
                            view: UserRolesView,
                            model: this.model
                        },
                        groups: {
                            caption: app.t('Groups'),
                            view: UserGroupsView,
                            model: this.model
                        }
                    }
                }));
            }
        );

        // fetch actual state
        this.model.fetch();
    },

    render: function () {
        this.$el.html(app.render('UserEditor', {
            user: this.model
        }));
    }
});
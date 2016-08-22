var UserEditorView = Backbone.View.extend({
    events: {
        'submit #parameters form': 'saveParametersEventHandler',
        'show.bs.tab [data-target="#roles"]': 'rolesTabClickEventHandler',
        'show.bs.tab [data-target="#groups"]': 'groupsTabClickEventHandler',
    },

    initialize: function () {
        // init model sync
        if (this.model.isNew()) {
            // fetch model
            this.listenToOnce(this.model, 'syncDefaults', this.renderAsync);
            this.model.fetchDefaults();
        } else {
            // fetch model
            this.listenToOnce(this.model, 'sync', this.renderAsync);
            this.model.fetch();
        }
    },

    renderAsync: function () {
        var self = this;

        if (!this.model.isNew()) {
            // current user not allowed to edit this user
            if (!this.model.hasPermission('edit')) {
                app.router.navigate('users/' + this.model.get('id'), {trigger: true});
                return;
            }
        }

        // render page
        this.$el.html(app.render('UserEditor', {
            user: this.model
        }));
    },

    saveParametersEventHandler: function (e) {
        var self = this;

        // prepare data
        var data = UrlMutator.unserializeQuery($('#parameters form').serialize());
        data['_token'] = app.csrf;

        // show preloader
        this.$el.find('.status').addClass('spinner-small');

        // remove prev error data
        this.$el.find('.help-block.error').remove();

        // save model
        this.model
            .save(null, {
                attrs: data
            })
            .always(function () {
                // hide preloader
                self.$el.find('.status').removeClass('spinner-small');
            })
            .done(function (response) {
                app.router.navigate('users/' + self.model.get('id'), {trigger: true});
            })
            .fail(function (xhr) {
                if (xhr.responseJSON.validation) {
                    var $input;
                    for (var fieldName in xhr.responseJSON.validation) {
                        $input = self.$el.find('INPUT[name="' + fieldName + '"]');
                        $input.closest('.form-group').addClass('has-error');
                        $input.after($('<div class="help-block error">').text(xhr.responseJSON.validation[fieldName]));

                        // hide error on key press
                        $input.keydown(function () {
                            $input.closest('.form-group').removeClass('has-error');
                            $(this).parent().find('.help-block.error').remove();
                        });
                    }
                }
            });

        return false;
    },

    rolesTabClickEventHandler: function (e) {
        var $tab = $(e.target);

        // load data when first click on tab
        if ($tab.data('loaded')) {
            return;
        }
        $tab.data('loaded', true);

        // render tab
        var userRolesView = new UserRolesView({
            el: '#roles',
            model: this.model
        });
        userRolesView.render();
    },

    groupsTabClickEventHandler: function (e) {
        var $tab = $(e.target);

        // load data when first click on tab
        if ($tab.data('loaded')) {
            return;
        }
        $tab.data('loaded', true);

        // render tab
        var userGroupsView = new UserGroupsView({
            el: '#groups',
            model: this.model
        });
        userGroupsView.render();
    }
});
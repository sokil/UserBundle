var UserRolesView = Backbone.View.extend({

    events: {
        'submit form': 'saveUserRoles'
    },

    roles: null,
    
    initialize: function() {
        var self = this;

        // fetch role hierarchy from server
        app.container.get('rolesPromise').done(function(response) {
            self.roles = response.roles;
            self.renderAsync();
        });
    },

    renderAsync: function() {
        this.$el.html(app.render('UserRoles', {
            user: this.model,
            roles: this.roles
        }));
    },
    
    saveUserRoles: function() {
        var self = this;

        var data = UrlMutator.unserializeQuery(this.$el.find('form').serialize());
        data['_token'] = app.csrf;

        // show preloader
        this.$el.find('.status').addClass('spinner-small');

        this.model
            .save(null, {
                attrs: data
            })
            .always(function () {
                // hide preloader
                self.$el.find('.status').removeClass('spinner-small');
            })
            .done(function(response) {
                
            });

        return false;
    }
});
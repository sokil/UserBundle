var UserGroupsView = Backbone.View.extend({

    events: {
        'submit form': 'saveUserGroups'
    },

    groups: null,
    
    initialize: function() {
        var self = this;

        // fetch role hierarchy from server
        app.container.get('roleGroupsPromise').done(function(response) {
            self.groups = response.groups;
            self.renderAsync();
        });
    },

    renderAsync: function() {
        if (this.groups.length === 0) {
            this.$el.html(app.render('UserGroupsEmptyList'));
            return;
        }

        this.$el.html(app.render('UserGroups', {
            user: this.model,
            groups: this.groups
        }));
    },
    
    saveUserGroups: function() {
        var self = this;

        var data = UrlMutator.unserializeQuery(this.$el.find('form').serialize());
        data['_token'] = app.csrf;

        if (!data.groups) {
            data.groups = [];
        }

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
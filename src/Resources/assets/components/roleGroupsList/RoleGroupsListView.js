var RoleGroupsListView = Backbone.View.extend({

    groups: null,

    events: {
        'click .edit': 'clickEditEventListener',
        'click .delete': 'clickDeleteEventListener'
    },
    
    initialize: function() {
        var self = this;

        // fetch role hierarchy from server
        if (!this.collection.models.length) {
            this.listenTo(this.collection, 'sync', this.renderAsync);
            this.collection.fetch();
        } else {
            this.renderAsync();
        }
    },

    renderAsync: function() {
        this.$el.html(app.render('RoleGroupsList', {
            groups: this.collection.models
        }));
    },

    clickEditEventListener: function(e) {
        var $a = $(e.currentTarget);
        var id = $a.data('id');

        var collection = app.container.get('roleGroupsCollection');
        var model = collection.get(id);

        if (model) {
            app.popup(new RoleGroupEditorPopupView({
                model: model
            }));
        } else {
            model = collection.add({id: id});
            model.on('sync', function() {
                app.popup(new RoleGroupEditorPopupView({
                    model: model
                }));
            });
            model.fetch();
        }
    },

    clickDeleteEventListener: function(e) {
        var $a = $(e.currentTarget);
        var id = $a.data('id');

        var collection = app.container.get('roleGroupsCollection');
        var model = collection.get(id);
        model.destroy().done(function() {
            $a.closest('[data-group]').remove();
        });
    }
});
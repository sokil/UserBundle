var UserView = Backbone.View.extend({
    events: {
        'click .delete': 'deleteEventListener'
    },

    initialize: function() {
        this.listenTo(this.model, 'sync', this.renderAsync);
        this.model.fetch();
    },

    renderAsync: function() {
        this.$el.html(app.render('User', {
            user: this.model
        }));
    },

    deleteEventListener: function(e) {
        this.model
            .destroy()
            .done(function(response) {
                app.router.navigate('users', {trigger: true});
            })
            .fail(function(xhr) {
                var response = xhr.responseJSON;
                alert(response.message);
            });
    }
});
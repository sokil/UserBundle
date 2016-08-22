var UserListView = Backbone.View.extend({

    events: {
        'click .delete': 'deleteEventListener'
    },

    initialize: function(options) {
        this.listenTo(this.collection, 'sync', this.renderAsync);
        this.collection
            .setLimit(20)
            .setPage(1)
            .fetchPage()
            .fail(function(response, xhr) {
                app.router.navigate('', {trigger: true});
            });
    },

    renderAsync: function() {
        var self = this;
        
        // render page
        this.$el.html(app.render('UserList', {
            users: this.collection.models
        }));

        // render paginator
        var pagination = new PaginationView({
            el: this.$el.find('.pagination-wrap'),
            itemCount: this.collection.usersCount,
            itemCountPerPage: this.collection.limit,
            currentPage: this.collection.page
        });
        
        pagination
            .on('change', function(e) {
                self.collection
                    .setPage(e.page)
                    .fetchPage();
            })
            .render();
    },

    deleteEventListener: function(e) {
        var self = this,
            $a = $(e.currentTarget),
            userId = $a.closest('[data-user-id]').data('user-id'),
            user = this.collection.get(userId);

        user
            .destroy()
            .done(function(response) {
                self.renderAsync();
            })
            .fail(function(xhr) {
            var response = xhr.responseJSON;
            alert(response.message);
        });
    }
});
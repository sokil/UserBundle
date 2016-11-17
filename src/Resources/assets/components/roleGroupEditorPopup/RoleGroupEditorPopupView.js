var RoleGroupEditorPopupView = PopupView.extend({

    events: {
        'click .save': 'saveRoleGroupEventHandler'
    },

    buttons: [
        {class: 'btn-primary save', title: 'Save'}
    ],

    roles: null,

    template: false,
    
    initialize: function() {
        var self = this;

        // fetch role hierarchy from server
        app.container.get('rolesPromise')
            .done(function(response) {
                self.roles = response.roles;

                if (self.model.isNew()) {
                    self.renderAsync();
                } else {
                    self.model.fetch().done(function() {
                        self.renderAsync();
                    });
                }
            });
    },

    renderAsync: function() {
        var self = this;
        var html = app.render('RoleGroupEditorPopup', {
            group: this.model,
            roles: this.roles
        });

        //init scroll
        app.loadCss([
            '/bundles/user/css/perfect-scrollbar.css'
        ]);

        require(['user/js/perfect-scrollbar'], function() {
            // show body
            self.setBody(html);
            // add scroll
            self.$el.find('.list')
                .css({
                    height: '300px',
                    position: 'relative'
                })
                .perfectScrollbar();
        })
    },
    
    saveRoleGroupEventHandler: function() {
        var data = UrlMutator.unserializeQuery(this.$el.find('form').serialize());
        data['_token'] = app.csrf;
        
        this.model
            .save(null, {
                attrs: data
            })
            .done(function(response) {
                
            });

        return false;
    }
});
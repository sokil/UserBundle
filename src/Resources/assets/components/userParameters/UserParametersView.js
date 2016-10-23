var UserParametersView = Backbone.View.extend({
    events: {
        'submit form': 'saveParametersEventHandler'
    },

    render: function() {
        this.$el.html(app.render('UserParameters', {
            user: this.model
        }));
    },

    saveParametersEventHandler: function (e) {
        var self = this;

        // prepare data
        var data = UrlMutator.unserializeQuery(this.$('form').serialize());
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
    }
});
var UserAttribute = Backbone.Model
    .extend({
        urlRoot: '/users/attributes',
        parse: function(response, options) {
            if (options.collection) {
                return response;
            } else {
                return response.attribute;
            }
        }
    })
    .extend(ModelFetchDefaultsTrait);
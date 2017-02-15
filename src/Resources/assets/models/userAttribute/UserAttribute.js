var UserAttribute = Backbone.Model
    .extend({
        urlRoot: '/users/attributes',

        availableTypes: [],

        parse: function(response, options) {
            if (options.collection) {
                this.availableTypes = options.collection.availableTypes;
                return response;
            } else {
                return response.attribute;
            }
        }
    })
    .extend(ModelFetchDefaultsTrait);
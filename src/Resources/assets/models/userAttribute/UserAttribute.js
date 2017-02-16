var UserAttribute = Backbone.Model
    .extend({
        urlRoot: '/users/attributes',

        formElements: {},

        parse: function(response, options) {
            if (options.collection) {
                this.formElements = options.collection.formElements[response.type];
                return response;
            } else {
                this.formElements = response.formElements;
                return response.attribute;
            }
        }
    })
    .extend(ModelFetchDefaultsTrait);
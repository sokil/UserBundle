var UserAttributeSchema = Backbone.Model
    .extend({
        urlRoot: '/users/attributes/schema',
        parse: function(response, options) {
            return response.schema;
        }
    });
var UserAttributeCollection = Backbone.Collection.extend({
    model: UserAttribute,
    url: '/users/attributes',

    parse: function(response) {
        return response.attributes;
    }
});
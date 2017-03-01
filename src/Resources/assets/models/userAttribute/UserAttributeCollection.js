var UserAttributeCollection = Backbone.Collection.extend({
    model: UserAttribute,
    url: '/users/attributes',

    availableTypes: [],

    parse: function(response) {
        this.availableTypes = response.availableTypes || [];
        return response.attributes;
    }
});
var UserAttributeCollection = Backbone.Collection.extend({
    model: UserAttribute,
    url: '/users/attributes',

    availableTypes: [],

    formElements: [],

    parse: function(response) {
        this.availableTypes = response.availableTypes || [];
        this.formElements = response.formElements || [];
        return response.attributes;
    }
});
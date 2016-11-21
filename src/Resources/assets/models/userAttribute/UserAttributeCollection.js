var UserAttributeCollection = Backbone.Collection.extend({
    model: UserAttribute,
    url: '/users/attributes'
});
var RoleGroupCollection = Backbone.Collection.extend({
    model: RoleGroup,
    url: '/roles/groups',
    parse: function(response) {
        return response.groups;
    }
});
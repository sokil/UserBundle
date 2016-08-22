var UserServiceDefinition = {
    /**
     * List of groups of users
     */
    roleGroupsCollection: function() {
        return new RoleGroupCollection();
    },

    /**
     * Promice of list of groups of users
     */
    roleGroupsPromise: function() {
        var collection = this.get('roleGroupsCollection');
        return collection.fetch();
    },

    /**
     * Roles list
     */
    rolesPromise: function() {
        return $.get('/roles');
    }
};
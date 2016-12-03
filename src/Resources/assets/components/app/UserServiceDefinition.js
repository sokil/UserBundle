var UserServiceDefinition = {
    /**
     * List of groups of users
     */
    roleGroupsCollection: function() {
        return new RoleGroupCollection();
    },

    userAttributeCollection: function() {
        return new UserAttributeCollection();
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
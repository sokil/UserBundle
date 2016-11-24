var UsersAttributesListView = ListView.extend({
    showColumnHeader: true,
    columns: function() {
        return [
            {
                caption: app.t('user_attribute.name'),
                name: 'name'
            },
            {
                caption: app.t('user_attribute.description'),
                name: 'description'
            },
            {
                caption: app.t('user_attribute.type'),
                name: 'type'
            },
            {
                caption: app.t('user_attribute.printFormat'),
                name: 'printFormat'
            },
            {
                caption: app.t('user_attribute.defaultValue'),
                name: 'defaultValue'
            }
        ];
    }
});
var UsersAttributesListView = ListView.extend({

    showColumnHeader: true,

    columns: function() {
        return [
            {
                caption: app.t('user_attribute_list.name'),
                name: 'name'
            },
            {
                caption: app.t('user_attribute_list.description'),
                name: 'description'
            },
            {
                caption: app.t('user_attribute_list.type'),
                name: 'type'
            },
            {
                caption: app.t('user_attribute_list.printFormat'),
                name: 'printFormat'
            },
            {
                caption: app.t('user_attribute_list.defaultValue'),
                name: 'defaultValue'
            }
        ];
    },

    buttons: function() {
        return [
            {
                name: 'edit',
                class: 'btn btn-default btn-xs',
                icon: 'glyphicon glyphicon-pencil',
                caption: app.t('user_attribute_list.edit'),
                click: function(e, attributeId, view) {
                    app.popup(new UserAttributeEditorPopupView({
                        model: view.collection.get(attributeId)
                    }));
                }
            }
        ];
    }
});
var User = Backbone.Model
    .extend({
        urlRoot: '/users',

        inGroup: function (groupId) {
            for (var i in this.get('groups')) {
                if (this.get('groups')[i].id === groupId) {
                    return true;
                }
            }

            return false;
        },

        getGravatarUrl: function (size, defaultImage, rating) {
            if (!size) {
                size = 80;
            }

            if (!defaultImage) {
                defaultImage = 'mm';
            }

            if (!rating) {
                rating = 'g';
            }

            return this.get('gravatar') + '?s=' + size + '&d=' + defaultImage + '&r=' + rating;
        },

        hasPermission: function (permission) {
            return this.get('permissions')[permission] === true;
        }
    })
    .extend(ModelFetchDefaultsTrait);
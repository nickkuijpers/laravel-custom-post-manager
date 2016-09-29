export default {
    methods: {

        /**
         * Showing the preloader
         */
        showPreloader() {
            $('.spinner').fadeIn(150);
        },

        /**
         * Hiding the preloader
         */
        hidePreloader() {
            $('.spinner').fadeOut(150);
        },
        /**
         * Validating and submitting the form
         */
        validateForm(e) {
            this.showPreloader();
            var form = e.target;

            var body = {}
            $(form).serializeArray().map(field => {
                body[field.name] = field.value;
            });

            // Making the post request
            this.$http.post(form.action, body).then((response) => {

                this.$parent.nikuCms.notification.message = 'Actie succesvol';
                this.$parent.nikuCms.notification.type = 'success';
                this.$parent.nikuCms.notification.display = 1;

                this.goToRoute('niku-cms-list-posts');
                this.hidePreloader();

            }, response => {

                // Displaying the error messages
                $('.validateError').remove();
                this.displayErrorMessages(form, response.json());

                this.$parent.nikuCms.notification.message = 'Controleer uw invoer';
                this.$parent.nikuCms.notification.type = 'danger';
                this.$parent.nikuCms.notification.display = 1;

                this.hidePreloader();
            });
        },

        /**
         * Displaying validation errors
         */
        displayErrorMessages(form, jsonResponse) {
            _.forIn(jsonResponse, (error, name) => {
                let helpBlock = '<span class="validateError help-block" style="color:red;">' + '<strong>' + error + '</strong>' + '</span>';
                $('#' + form.id + ' #' + name).after(helpBlock);
            });
        },

        /**
         * Resetting notification on each view
         */
        reset() {
            this.$parent.nikuCms.notification.display = 0;
        },

        /**
         * Ordering the posts
         */
        orderingBy(name) {
            if( name == this.orderName ){
                this.order = this.order * -1;
            } else {
                this.order = 1;
            }
            this.orderName = name;
        },

        /**
         * Routing
         */
        goToRoute(route, data) {
            this.$parent.nikuCms.view = route;
            this.$parent.nikuCms.data = data;
        },

        /**
         * Linking to a new post creation post
         */
        newPost() {
            this.goToRoute('niku-cms-single-post');
        },

    },
    directives: {
        // Sanitizing URL
        url() {
            this.handler = function () {

                var str = this.el.value;
                str = str.toLowerCase();

                // remove accents, swap ñ for n, etc
                var from = "ãàáäâẽèéëêìíïîõòóöôùúüûñç·/_,:;";
                var to = "aaaaaeeeeeiiiiooooouuuunc------";
                for (var i = 0, l = from.length; i < l; i++) {
                    str = str.replace(new RegExp(from.charAt(i), 'g'), to.charAt(i));
                }

                str = str.replace(/[^a-z0-9 -]/g, '') // remove invalid chars
                    .replace(/\s+/g, '-') // collapse whitespace and replace by -
                    .replace(/-+/g, '-'); // collapse dashes

                this.el.value = str;
                // this.set(this.el.value);
            }.bind(this);
            this.el.addEventListener('input', this.handler);
        }
    },
    filters: {
        url(str) {

            str = str.toLowerCase();

            // remove accents, swap ñ for n, etc
            var from = "ãàáäâẽèéëêìíïîõòóöôùúüûñç·/_,:;";
            var to = "aaaaaeeeeeiiiiooooouuuunc------";
            for (var i = 0, l = from.length; i < l; i++) {
                str = str.replace(new RegExp(from.charAt(i), 'g'), to.charAt(i));
            }

            str = str.replace(/[^a-z0-9 -]/g, '') // remove invalid chars
                .replace(/\s+/g, '-') // collapse whitespace and replace by -
                .replace(/-+/g, '-'); // collapse dashes

            return str;
        }
    }
}

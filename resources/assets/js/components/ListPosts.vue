<template>
    <div class="niku-cms" v-if="authorized == 1">

        <div class="row">
            <div class="col-sm-8 col-md-8">
            <h3>{{ label }}</h3>
            </div>
            <div class="col-sm-4 col-md-4">
                <a @click="newPost()" class="btn btn-default pull-right">Nieuwe pagina</a>
            </div>
        </div>

        <div class="listing">
            <div class="row">
                <div class="col-md-8">
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <input v-model="filterInput" class="form-control" placeholder="Filter...">
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th class="pointer" width="8%" @click="orderingBy('id')">ID &#x21D5</th>
                            <th class="pointer" width="30%" @click="orderingBy('post_title')">NAAM &#x21D5</th>
                            <th class="pointer" width="30%" @click="orderingBy('post_name')">URL &#x21D5</th>
                            <th class="pointer" width="12%" @click="orderingBy('post_type')">TYPE &#x21D5</th>
                            <th class="pointer" width="18%" @click="orderingBy('status')">STATUS &#x21D5</th>
                            <th width="1"></th>
                            <th width="1"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="object in objects | filterBy filterInput | orderBy orderName order">
                            <td>{{ object.id }}</td>
                            <td>{{ object.post_title }}</td>
                            <td>{{ object.post_name }}</td>
                            <td>{{ object.post_type }}</td>
                            <td>{{ object.status }}</td>
                            <td>
                                <a @click="editPost(object, object.id)" class="pointer">Wijzig</a>
                            </td>
                            <td>
                                <a @click="deletePost(object, object.id)" class="pointer">Verwijder</a>
                            </td>
                        </tr>
                        <tr v-show="!objects.length">
                            <td colspan="8">
                                <p class="noresults">Geen berichten gevonden</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</template>

<script>
export default {
    ready () {

        /**
         * Initialing default data
         */
        this.nikuCms = this.$parent.nikuCms;
        this.$parent.nikuCms.postType = this.post_type;
        // this.token = $('meta[name=_token]').attr('content');
        this.displayNotification = this.nikuCms.notification.display;

        // // Beginning of CMS
        this.receiveJson();
    },
    mixins: [require('../mixins.js')],
    data () {
        return {
            'nikuCms': '',
            'displayNotification': 0,
            'objects': [],
            'label': '',
            'orderName': 'post_title',
            'order': 1,
            'filterInput': '',
            'authorized': 1
        }
    },
    props: {
        'post_type': {
          default: 'page'
        },
    },
    methods: {

        /**
         * Receiving all posts
         */
        receiveJson() {
            if(this.displayNotification == 0){
                this.showPreloader();
            }
            this.$http.get('/niku-cms/' + this.post_type)
                .then(response => {

                    if(response.data.code == 'error'){

                        this.authorized = 0;

                        // Send a notification that there is a error
                        this.$parent.nikuCms.notification.message = response.data.status;
                        this.$parent.nikuCms.notification.type = 'danger';
                        this.$parent.nikuCms.notification.display = 1;

                    } else {
                        this.authorized = 1;
                        this.objects = response.data.objects;
                        this.label = response.data.label;
                    }

                    this.hidePreloader();
                }
            );
        },

        /**
         * Showing the form with the post to edit it
         */
        editPost(object, id) {
            this.goToRoute('niku-cms-single-post', {
                'type': 'edit',
                'postId': id
            })
        },

        /**
         * Going to a new post
         */
        newPost() {
            this.goToRoute('niku-cms-single-post', {
                'type': 'new'
            })
        },

        /**
         * Deleting the post with validation
         */
        deletePost(object, id) {
            if(confirm("Weet je het zeker?")) {
                this.showPreloader();
                this.$http.delete('/niku-cms/' + this.post_type + '/delete/' + id)
                    .then(response => {
                        this.objects.$remove(object);
                        this.hidePreloader();
                    }
                );
            }
        },

    },
}
</script>

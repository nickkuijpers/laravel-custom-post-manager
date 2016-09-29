<template>
    <div class="niku-cms">

        <div class="row">
            <div class="col-sm-8 col-md-8">
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
                <table class="table">
                    <thead>
                        <tr>
                            <th width="8%" @click="orderingBy('id')">ID <i class="fa fa-sort"></i></th>
                            <th width="40%" @click="orderingBy('post_title')">NAAM <i class="fa fa-sort"></i></th>
                            <th width="40%" @click="orderingBy('post_name')">URL <i class="fa fa-sort"></i></th>
                            <th width="12%" @click="orderingBy('post_type')">TYPE <i class="fa fa-sort"></i></th>
                            <th width="18%" @click="orderingBy('status')">STATUS <i class="fa fa-sort"></i></th>
                            <th width="1"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="object in objects |  filterBy filterInput | orderBy orderName order">
                            <td>{{ object.id }}</td>
                            <td>{{ object.post_title }}</td>
                            <td>{{ object.post_name }}</td>
                            <td>{{ object.post_type }}</td>
                            <td>{{ object.status }}</td>
                            <td>
                                <table>
                                    <tr>
                                        <td><a @click="editPost(object, object.id)" class="pointer"><i class="fa fa-fw fa-edit"></i></a></td>
                                        <td><a @click="deletePost(object, object.id)" class="pointer"><i class="fa fa-fw fa-remove"></i></a></td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr v-show="!objects.length">
                            <td colspan="4">
                                <p>Geen berichten gevonden</p>
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
        this.token = $('meta[name=_token]').attr('content');
        this.displayNotification = this.nikuCms.notification.display;

        // Beginning of CMS
        this.receiveJson();
    },
    mixins: [require('../mixins.js')],
    data () {
        return {
            'nikuCms': '',
            'displayNotification': 0,
            'token': '',
            'objects': [],
            'orderName': 'post_title',
            'order': 1,
            'filterInput': '',
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
                    this.objects = response.data;
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
                this.$http.delete('/niku-cms/delete/' + id)
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

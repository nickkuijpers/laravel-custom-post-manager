<template>
    <div class="niku-cms">

        <div class="row">
            <div class="col-md-12">
                <a @click="backToList()" class="btn btn-default" style="margin-bottom:20px;">Terug naar lijstweergave</a>
            </div>
        </div>
        <div class="row">

            <form method="post" action="/niku-cms/{{ postType }}/{{ postAction }}" class="form-horizontal" @submit.prevent="validateForm" id="newPost" data-stay="true">
                <input type="hidden" name="_posttype" value="{{ postType }}">
                <input type="hidden" name="_id" value="{{ post.id }}">

                <div class="col-md-9">
                    <h3>{{ pageTitle }} {{ post.post_title }}</h3>

                    <hr>

                    <!-- post_title -->
                    <div class="form-group">
                        <label for="post_title" class="col-sm-3 control-label">Titel:</label>
                        <div class="col-sm-6">
                            <input type="text" v-model="post.post_title" name="post_title" id="post_title" class="form-control" value="">
                        </div>
                    </div>

                    <!-- post_name -->
                    <div class="form-group">
                        <label for="post_name" class="col-sm-3 control-label">Slug:</label>
                        <div class="col-sm-6">
                            <input type="text" name="post_name" v-model="post.post_name" id="post_name" class="form-control" v-url="post.post_name" value="{{ post.post_title | url }}">
                        </div>
                    </div>

                    <!-- post_content -->
                    <div class="form-group">
                        <label for="post_content" class="col-sm-3 control-label">Inhoud:</label>
                        <div class="col-sm-9">
                            <textarea name="post_content" v-model="post.post_content" id="post_content" class="form-control" rows="6"></textarea>
                        </div>
                    </div>

                    <!-- Dynamicly including all required components for the custom fields -->
                    <template v-for="customField in customFields">
                        <component :is="customField.component" :data="customField"></component>
                    </template>

                </div>
                <div class="col-md-3">
                    <div class="row">
                        <div class="col-md-12">
                            <div id="niku-cms-sidebar">
                                <div class="panel panel-default">
                                    <div class="panel-body">

                                        <!-- post_status -->
                                        <div class="form-group">
                                            <div class="col-md-12">
                                                <label for="status" class="control-label">Status:</label>
                                                <select name="status" v-model="post.status" id="status" class="form-control">
                                                    <option value="1">Gepubliceerd</option>
                                                    <option value="0">Concept</option>
                                                </select>
                                            </div>
                                        </div>

                                         <!-- post_status -->
                                        <template v-if="templateCount = 1">
                                            <input type="hidden" name="template" value="default">
                                        </template>
                                        <div class="form-group" v-if="templatesCount > 1">
                                            <div class="col-md-12">
                                                <label for="template" class="control-label">Template:</label>
                                                <select name="template" v-model="post.template" id="template" @change="rerender" class="form-control">
                                                    <option v-for="template in templates" value="{{ template.template }}">
                                                        {{ template.label }}
                                                    </option>
                                                </select>
                                            </div>
                                        </div>

                                        <button type="submit" class="btn btn-default pull-right">{{ buttonLabel }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

    </div>

</template>

<script>
export default {
    data () {
        return {
            'nikuCms': '',
            'token': '',
            'postAction': '',
            'pageTitle': '',
            'postType': '',
            'buttonLabel': '',
            'post': {
                'postId': '',
                'post_name': '',
                'post_title': '',
                'post_content': '',
                'status': '',
                'template': '',
            },
            'view': {},
            'customFields': {},
            'templatesCount': '',
            'templates': ''
        }
    },
    ready () {
        this.showPreloader();
        this.reset();

        // Recieving old data
        this.nikuCms = this.$parent.nikuCms;
        this.postType = this.nikuCms.postType;
        this.post.postId = this.nikuCms.data.postId;

        // Switching between type of request
        if(this.nikuCms.data.type == 'edit'){
            this.editPost(this.postId);

        } else {
            this.newPost();
            this.post.template = 'default';
        }

        this.token = $('meta[name=_token]').attr('content');
        this.hidePreloader();
    },
    mixins: [require('../mixins.js')],
    methods: {

        /**
         * Showing the form for post creation
         */
        newPost() {
            this.postAction = 'create';
            this.buttonLabel = 'Toevoegen';
            this.pageTitle = 'Nieuw object:';
            this.$dispatch('recieveViewEvent', '');
        },

        /**
         * Showing the form with the post to edit it
         */
        editPost() {
            this.postAction = 'edit';

            this.$http.get('/niku-cms/' + this.postType + '/show/' + this.post.postId)
                .then(response => {

                    this.pageTitle = 'Object bewerken:';
                    this.buttonLabel = 'Wijzigen';

                    // Receive content from post API
                    this.post = response.data.post;
                    this.$dispatch('recieveViewEvent', '');
                }
            );
        },

        /**
         * Rerendering the custom fields when the template changes
         */
        rerender() {
            this.customFields = this.templates[this.post.template].customFields;
        },

        /**
         * Recieving the custom fields based on page template and post type
         */
        receiveView() {

            this.$http.post('/niku-cms/' + this.postType + '/receiveview/', {
                '_post_type': this.postType,
                '_id': this.post.id,
            }).then(response => {

                    // Receive content from post API and validating if its active
                    if(response.data.code == 'doesnotexist'){

                        // Send a notification that there is a error
                        this.$parent.nikuCms.notification.message = 'Post type does not exist.';
                        this.$parent.nikuCms.notification.type = 'danger';
                        this.$parent.nikuCms.notification.display = 1;

                    } else {

                        this.view = response.data;
                        this.templates = this.view.templates;
                        this.customFields = this.templates[this.post.template].customFields;
                        this.templatesCount = Object.keys(this.templates).length;

                    }

                }
            );

        },

        /**
         * Link back to list view
         */
        backToList() {
            this.goToRoute('niku-cms-list-posts');
        }

    },
    events: {
        'recieveViewEvent': function () {
            // Recieving custom fields
            this.receiveView();
        }
    }
}
</script>

<template>

    <div>


        <div id="media-modal" class="modal fade niku-cms-mediamanager">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-12 mediamanager-modal-header">
                                <div class="row">
                                    <div class="col-md-2 top-left-sidebar">
                                        <div class="modal-title">
                                            <h4>Media bibliotheek</h4>
                                        </div>
                                    </div>
                                    <div class="col-md-9 top-main-content">
                                        <ul class="nav nav-tabs">
                                            <li v-bind:class="{ active: viewUpload }"><a v-on:click="changeViewUpload()">Upload</a></li>
                                            <li v-bind:class="{ active: viewLibrary }"><a v-on:click="changeViewLibrary()">Bibliotheek</a></li>
                                        </ul>
                                    </div>
                                    <div class="col-md-1 top-right-sidebar">
                                        <button class="close" data-target="#media-modal" data-dismiss="modal">&times;</button>
                                    </div>
                                </div>
                            </div>
                                <div class="mediamanager-modal-content">
                                <div class="col-md-2 left-sidebar">

                                </div>
                                <div class="col-md-10 main-content">
                                    <div class="row">
                                        <div class="col-md-10 inner-main-content">

                                            <div class="modal-body">
                                                <div class="tab-content">
                                                    <div class="tab-pane" v-show="viewUpload" id="upload">
                                                        <niku-cms-dropzone-mediamanager id="niku-dropzone" url="/niku-cms/media"></niku-cms-dropzone-mediamanager>
                                                    </div>
                                                    <div class="tab-pane medialibrary" v-show="viewLibrary" id="library">

                                                        <div class="row">

                                                            <template v-for="(index, object) in objects">
                                                                <div class="col-md-2 image" v-on:click="selectedImage(index)">
                                                                    <div v-bind:class="{ 'active': object.status == '1' }">
                                                                        <div class="image-wrapper">
                                                                            <img v-bind:src="object.postmeta[0].meta_value" alt="" class="img-responsive">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </template>

                                                        </div>

                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                        <div class="col-md-2 inner-right-sidebar">

                                        </div>
                                        <div class="col-md-12 inner-bottom-bar">
                                            <div class="row">
                                                <div class="col-md-9 bottom-main-content">

                                                </div>
                                                <div class="col-md-3 bottom-right-sidebar">
                                                    <a v-on:click="insertImage()" class="btn btn-default">Selecteren</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <a class="click" style="display:none"; data-toggle="modal" data-target="#media-modal"></a>

    </div>

</template>

<script>
export default {
    ready () {
        this.receiveAttachments();
        nikuCms.$on('mediamanager', function(object){
           this.triggerId = object.id;
           $(".click").click()
        }.bind(this));
    },
    data () {
      return {
        viewUpload: 1,
        triggerId: '',
        viewLibrary: 0,
        objects: [],
        imageSelected: []
      }
    },
    methods: {
        changeViewUpload() {
            this.view = 'upload';
            this.viewLibrary = 0;
            this.viewUpload = 1;
        },
        changeViewLibrary() {
            this.view = 'library';
            this.viewLibrary = 1;
            this.viewUpload = 0;
        },
        receiveAttachments() {
            this.$http.get('/niku-cms/attachment')
                .then(response => {
                    if(response.data.code == 'error'){
                        this.authorized = 0;
                        this.$parent.nikuCms.notification.message = response.data.status;
                        this.$parent.nikuCms.notification.type = 'danger';
                        this.$parent.nikuCms.notification.display = 1;
                    } else {
                        this.objects = response.data.objects;
                    }
                    this.hidePreloader();
                }
            );
        },
        selectedImage(index) {
            $(".image > div.active").removeClass("active");
            this.objects[index].status = 0;
            var currentObject = this.objects[index];
            if(currentObject.status == '1'){
                this.objects[index].status = 0;
            } else {
                this.objects[index].status = 1;
                this.imageSelected = this.objects[index];
            }
        },
        insertImage() {
            nikuCms.$emit('mediamanager-' + this.triggerId, this.imageSelected);
            $("#media-modal .close").click()
        }

    },
    mixins: [require('../mixins.js')],
    events: {
      'vdropzone-success': function (returnObject) {
        this.objects.push(returnObject.response.object);
      },
      'vdropzone-fileAdded': function (file) {
        this.changeViewLibrary()
      },
      'vdropzone-error': function (file) {
        changeViewUpload()
      }
    }
}
</script>

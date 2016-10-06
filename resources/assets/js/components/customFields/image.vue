<template>

    <div class="form-group">
        <label for="post_name" class="col-sm-3 control-label">{{ data.label }}:</label>
        <div v-if="showImage" class="col-md-1">
            <img v-bind:src="imageUrl" class="img-responsive">
        </div>
        <div class="col-sm-5">
            <a v-on:click="mediamanager()" v-show="uploadButtonShow" style="float:left;"  class="btn btn-default">Selecteer of upload afbeelding</a>
            <a v-on:click="deleteImage()" v-show="deleteButtonShow" style="float:left;" class="btn btn-danger">Verwijder afbeelding</a>
        </div>
    </div>

    <input type="hidden" name="{{ data.id }}" v-model="input" value="{{ data.value }}">

</template>

<script>
export default {
    ready () {
        this.input = '';

        if( this.data.value != ''){
            this.retrievedObject = JSON.parse(this.data.value);
            this.imageUrl = this.retrievedObject.url;
            this.showImage = 1;
            this.deleteButtonShow = 1;
            this.uploadButtonShow = 0;
            this.input = {
                'id': this.retrievedObject.id,
                'url': this.retrievedObject.url
            }
            this.input = JSON.stringify(this.input);
        }

        nikuCms.$on('mediamanager-' + this.data.id, function(object){

                // Receive object
                this.imageSelected = object;

                // Prepare data for database and jsonfy it
                this.input = {
                    'id': this.imageSelected.id,
                    'url': this.imageSelected.postmeta[0].meta_value
                }
                this.input = JSON.stringify(this.input);

                // Change view
                this.uploadButtonShow = 0;
                this.deleteButtonShow = 1;
                this.showImage = 1;

        }.bind(this));

    },
    data () {
        return {
            input: '',
            displayMediaManager: 0,
            imageSelected: '',
            imageUrl: '',
            uploadButtonShow: 1,
            deleteButtonShow: 0,
            showImage: 0,
            retrievedObject: ''
        }
    },
    props: {
        'data': ''
    },
    methods: {
        mediamanager() {
            nikuCms.$emit('mediamanager', {
                'id': this.data.id
            });
        },
        deleteImage() {
            this.imageUrl = '';
            this.input = '';
            this.uploadButtonShow = 1;
            this.deleteButtonShow = 0;
            this.showImage = 0;
        }
    }
}
</script>

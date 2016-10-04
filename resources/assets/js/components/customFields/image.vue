<template>

    <div class="form-group">
        <label for="post_name" class="col-sm-3 control-label">{{ data.label }}:</label>
        <div v-if="showImage" class="col-md-1">
            <img v-bind:src="imageUrl" class="img-responsive">
        </div>
        <div class="col-sm-5">
            <a v-on:click="mediamanager()" v-show="uploadButtonShow" data-toggle="modal" style="float:left;" data-target="#media-modal" class="btn btn-default">Selecteer of upload afbeelding</a>
            <a v-on:click="deleteImage()" v-show="deleteButtonShow" style="float:left;" data-target="#media-modal" class="btn btn-danger">Verwijder afbeelding</a>
            <niku-cms-media-manager></niku-cms-media-manager>
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
            this.displayMediaManager = 1;
        },
        deleteImage() {
            this.imageUrl = '';
            this.input = '';
            this.uploadButtonShow = 1;
            this.deleteButtonShow = 0;
            this.showImage = 0;
        }
    },
    events: {
        'imageSelected': function (image) {
            this.imageSelected = image;
            this.imageUrl = this.imageSelected.postmeta[0].meta_value;
            this.input = {
                'id': this.imageSelected.id,
                'url': this.imageSelected.postmeta[0].meta_value
            }
            this.input = JSON.stringify(this.input);
            this.uploadButtonShow = 0;
            this.deleteButtonShow = 1;
            this.showImage = 1;
        }
    }
}
</script>

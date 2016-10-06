<template>

    <div class="form-group">
        <label for="post_name" class="col-sm-3 control-label">{{ data.label }}:</label>
        <div class="col-sm-9">
            <textarea name="{{ data.id }}" v-model="input" rows="8" class="form-control niku-editor">{{ data.value }}</textarea>
        </div>
    </div>

</template>

<script>
export default {
    data () {
        return {
            'input': '',
        }
    },
    props: {
        'data': '',
        'imageUrl': ''
    },
    created () {

    },
    ready () {

        function nikuFileManager (field_name, url, type, win) {

            nikuCms.$emit('mediamanager', {
                'id': field_name
            });

            nikuCms.$on('mediamanager-' + field_name, function(object){

                // Receive object
                this.imageSelected = object;

                this.imageUrl = this.imageSelected.postmeta[0].meta_value;
                win.document.getElementById(field_name).value = this.imageUrl;
            });

        }

        function myCustomURLConverter (url, node, on_save, name) {
            return url;
        }

        tinymce.init({
            selector:'.niku-editor',
            theme: "modern",
            skin: 'light',
            plugins: "image imagetools",
            urlconverter_callback : myCustomURLConverter,
            // relative_urls : true,
            document_base_url : '/',
            convert_urls: false,
            file_browser_callback : nikuFileManager,
            setup: function (editor) {
                editor.on('change', function () {
                    editor.save();
                });
            }
        });

    }
}
</script>

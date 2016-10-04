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
		'data': ''
	},
    created () {

    },
	ready () {
        tinymce.init({
            selector:'.niku-editor',
            theme: "modern",
            skin: 'light',
            plugins: "image imagetools",
            setup: function (editor) {
                editor.on('change', function () {
                    editor.save();
                });
            }
        });

	}
}
</script>

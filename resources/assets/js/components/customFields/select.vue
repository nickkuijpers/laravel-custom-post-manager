<template>

	<div class="form-group">
		<label for="post_name" class="col-sm-2 control-label">{{ data.label }}:</label>
		<div class="col-sm-6">
			<input type="text" name="{{ data.id }}" v-model="input" name="{{ data.id }}" class="form-control" value="{{ data.value }}">
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
	ready () {
	}
}
</script>

<template>

	<div class="form-group">
        <div class="col-md-3">
            <label for="status" class="control-label">{{ data.label }}:</label>
        </div>
        <div class="col-md-9">
            <select name="{{ data.id }}" v-model="data.value" id="status" class="form-control">
                <option v-for="(value, key) in data.options" value="{{ key }}">
                    {{ value }}
                </option>
            </select>
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

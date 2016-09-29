// Default required vue comonents
Vue.component('niku-cms-notification', require('./components/Notification.vue'));
Vue.component('niku-cms-spinner', require('./components/Spinner.vue'));
Vue.component('niku-cms-list-posts', require('./components/ListPosts.vue'));
Vue.component('niku-cms-single-post', require('./components/SinglePost.vue'));
// Importing custom and default custom fields for the CMS
Vue.component('niku-cms-select-customfield', require('./components/customFields/select.vue'));
Vue.component('niku-cms-text-customfield', require('./components/customFields/text.vue'));
Vue.component('niku-cms-textarea-customfield', require('./components/customFields/textarea.vue'));
Vue.component('niku-cms-datepicker-customfield', require('./components/customFields/datepicker.vue'));

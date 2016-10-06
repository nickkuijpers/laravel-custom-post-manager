/**
 * Event bus
 *
 */
window.nikuCms = new Vue();
// var Dropzone = require('./vendor/dropzone')

// Default required vue comonents
Vue.component('niku-cms-notification', require('./components/Notification.vue'));
Vue.component('niku-cms-spinner', require('./components/Spinner.vue'));
Vue.component('niku-cms-list-posts', require('./components/ListPosts.vue'));
Vue.component('niku-cms-single-post', require('./components/SinglePost.vue'))
Vue.component('niku-cms-media-manager', require('./components/MediaManager.vue'));
// Importing custom and default custom fields for the CMS
Vue.component('niku-cms-select-customfield', require('./components/customFields/select.vue'));
Vue.component('niku-cms-image-customfield', require('./components/customFields/image.vue'));
Vue.component('niku-cms-text-customfield', require('./components/customFields/text.vue'));
Vue.component('niku-cms-textarea-customfield', require('./components/customFields/textarea.vue'));

Vue.component('niku-cms-dropzone-mediamanager', require('./components/mediaManager/dropzone.vue'));



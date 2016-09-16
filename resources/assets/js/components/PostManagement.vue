<style scoped>
    .pointer,
    .pointer .fa,
    .niku-cms-table th {
        cursor: pointer;    
    }    
    .fa-remove {
        color:red;
    }
    .niku-cms .row {
        margin-bottom:10px;
    }
    .niku-cms h2 {
        margin-bottom:25px;
    }

    .niku-cms tbody tr td {
        font-size:13px;
    }

    /*spinner*/
    .spinner {
      display: none;
      position: fixed;
      top: 0;
      bottom: 0;
      background: rgba(0, 0, 0, 0.6);
      left: 0;
      right: 0;
      text-align: center;
      z-index: 1040;
      overflow: hidden; }

    .spinner #loader {
      display: block;
      position: fixed;
      left: 50%;
      top: 50%;
      width: 150px;
      height: 150px;
      margin: -75px 0 0 -75px;
      border-radius: 50%;
      border: 3px solid transparent;
      border-top-color: #1867a8;
      /* Chrome, Opera 15+, Safari 5+ */
      -webkit-animation: spin 2s linear infinite;
      animation: spin 2s linear infinite;
      /* Chrome, Firefox 16+, IE 10+, Opera */ }

    .spinner #loader:before {
      content: "";
      position: absolute;
      top: 5px;
      left: 5px;
      right: 5px;
      bottom: 5px;
      border-radius: 50%;
      border: 3px solid transparent;
      border-top-color: #94c420;
      /* Chrome, Opera 15+, Safari 5+ */
      -webkit-animation: spin 3s linear infinite;
      animation: spin 3s linear infinite;
      /* Chrome, Firefox 16+, IE 10+, Opera */ }

    .spinner #loader:after {
      content: "";
      position: absolute;
      top: 15px;
      left: 15px;
      right: 15px;
      bottom: 15px;
      border-radius: 50%;
      border: 3px solid transparent;
      border-top-color: #fff;
      /* Chrome, Opera 15+, Safari 5+ */
      -webkit-animation: spin 1.5s linear infinite;
      animation: spin 1.5s linear infinite;
      /* Chrome, Firefox 16+, IE 10+, Opera */ }

    @-webkit-keyframes spin {
      0% {
        /* Chrome, Opera 15+, Safari 3.1+ */
        /* IE 9 */
        -webkit-transform: rotate(0deg);
        transform: rotate(0deg);
        /* Firefox 16+, IE 10+, Opera */ }
      100% {
        /* Chrome, Opera 15+, Safari 3.1+ */
        /* IE 9 */
        -webkit-transform: rotate(360deg);
        transform: rotate(360deg);
        /* Firefox 16+, IE 10+, Opera */ } }

    @keyframes spin {
      0% {
        /* Chrome, Opera 15+, Safari 3.1+ */
        /* IE 9 */
        -webkit-transform: rotate(0deg);
        transform: rotate(0deg);
        /* Firefox 16+, IE 10+, Opera */ }
      100% {
        /* Chrome, Opera 15+, Safari 3.1+ */
        /* IE 9 */
        -webkit-transform: rotate(360deg);
        transform: rotate(360deg);
        /* Firefox 16+, IE 10+, Opera */ } 
    }

    .niku-cms .btn {
        margin-bottom:15px;
    }
</style>

<template>
    <div class="niku-cms">
        <div v-show="notificationShow">
            <div class="alert alert-{{ notificationType }}">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                {{ notificationMessage }}
            </div>
        </div>
        <div class="row">
            <div class="col-sm-8 col-md-8">                
            </div>
            <div class="col-sm-4 col-md-4">
                <a @click="newPost()" v-show="displayNewButton" class="btn btn-default pull-right">Nieuwe pagina</a>
            </div>
        </div>
        <div class="row" v-show="displayForm">            
            <div class="col-md-12">
                <a @click="backToList()" class="btn btn-default" style="margin-bottom:20px;">Terug naar lijstweergave</a> 
                
                <h2>{{ pageTitle }} {{ post_title }}</h2>               

                <form method="post" action="/niku-cms/{{ post_type }}/{{ postAction }}" class="form-horizontal" @submit.prevent="validateForm" id="newPost" data-stay="true">                        
                    <input type="hidden" name="_token" value="{{ token }}">                       
                    <input type="hidden" name="_id" value="{{ postId }}">       

                    <div class="form-group">
                        <label for="post_title" class="col-sm-2 control-label">Titel:</label>
                        <div class="col-sm-6">
                            <input type="text" v-model="post_title" name="post_title" id="post_title" class="form-control" value="">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="post_name" class="col-sm-2 control-label">Slug:</label>
                        <div class="col-sm-6">
                            <input type="text" name="post_name" v-model="post_name" id="post_name" class="form-control" v-url="post_name" value="{{ post_title | url }}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="status" class="col-sm-2 control-label">Status:</label>
                        <div class="col-sm-6">
                            <select name="status" v-model="status" id="status" class="form-control" required="required">
                                <option value="1">Gepubliceerd</option>
                                <option value="0">Concept</option>                                
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="post_content" class="col-sm-2 control-label">Inhoud:</label>
                        <div class="col-sm-10">
                            <textarea name="post_content" v-model="post_content" id="post_content" class="form-control" rows="6"></textarea>
                        </div>
                    </div>                    

                    <div class="col-md-12">
                        <button type="submit" class="btn btn-default pull-right">{{ buttonLabel }}</button>
                    </div>

                </form>
                
            </div>
        </div>        

        <div class="listing" v-show="displayList">
            <div class="row">
                <div class="col-md-8">                
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <input v-model="filterInput" class="form-control" placeholder="Filter...">
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th width="40%" @click="orderingBy('post_title')">NAAM <i class="fa fa-sort"></i></th>
                            <th width="40%" @click="orderingBy('post_name')">URL <i class="fa fa-sort"></i></th>
                            <th width="12%" @click="orderingBy('post_type')">TYPE <i class="fa fa-sort"></i></th>
                            <th width="18%" @click="orderingBy('status')">STATUS <i class="fa fa-sort"></i></th>
                            <th width="1"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="object in objects |  filterBy filterInput | orderBy orderName order">                
                            <td>{{ object.post_title }}</td>
                            <td>{{ object.post_name }}</td>
                            <td>{{ object.post_type }}</td>
                            <td>{{ object.status }}</td>                
                            <td>
                                <table>
                                    <tr>
                                        <td><a @click="editPost(object, object.id)" class="pointer"><i class="fa fa-fw fa-edit"></i></a></td>
                                        <td><a @click="deletePost(object, object.id)" class="pointer"><i class="fa fa-fw fa-remove"></i></a></td>                                
                                    </tr>
                                </table>                              
                            </td>
                        </tr>
                        <tr v-show="!objects.length">
                            <td colspan="4">
                                <p>Geen berichten gevonden</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="spinner">
        <div id="loader"></div>
    </div>

</template>

<script>
export default {
    ready () {
        this.receiveJson();
        this.token = $('meta[name=_token]').attr('content');        
    },        
    data () {
        return {
            'token': '',
            'objects': [],
            'orderName': 'post_title',
            'order': 1,
            'filterInput': '',
            'displayForm': 0,
            'displayList': 1,
            'displayNewButton': 1,
            'notificationMessage': '',
            'notificationShow': 0,
            'notificationType': 'danger',
            'post_name': '',
            'post_title': '',
            'post_content': '',
            'status': '',
            'pageTitle': '',
            'buttonLabel': '',
            'postId': '',
            'postAction': 'create'
        }
    },
    props: {        
        'post_type': {
          default: 'page'
        },        
    },
    methods: {                

        /**
         * Receiving all posts
         */
        receiveJson() {                  
            showPreloader();                  
            this.$http.get('/niku-cms/' + this.post_type)
                .then(response => {
                    this.objects = response.data;                    
                    hidePreloader();
                }
            );     
        },

        /**
         * Ordering the posts
         */
        orderingBy(name) {                        
            if( name == this.orderName ){
                this.order = this.order * -1;                
            } else {
                this.order = 1;                                
            }            
            this.orderName = name;
        },

        /**
         * Showing the form for post creation
         */
        newPost() {            
            this.postAction = 'create';
            this.buttonLabel = 'Toevoegen';
            this.pageTitle = 'Nieuw object:';
            this.displayNewObject();
        },

        /**
         * Showing the form with the post to edit it
         */
        editPost(object, id) {
            showPreloader();                           
            this.postAction = 'edit';
            this.$http.get('/niku-cms/show/' + id)
                .then(response => {

                    this.postId = id;   
                    this.pageTitle = 'Object bewerken:';
                    this.buttonLabel = 'Wijzigen';                    

                    console.log(response.data);

                    // Receive content from post API
                    this.post_title = response.data.post_title;
                    this.post_name = response.data.post_name;
                    this.status = response.data.status;
                    this.post_content = response.data.post_content;

                    this.displayNewObject();
                    hidePreloader();
                }                
            );               
        },

        /**
         * Deleting the post with validation
         */
        deletePost(object, id) {            
            if(confirm("Weet je het zeker?")) {
                showPreloader();
                this.$http.delete('/niku-cms/delete/' + id)
                    .then(response => {                              
                        this.objects.$remove(object);                      
                        hidePreloader();
                    }
                );     
            }
        },

        /**
         * Validating and submitting the form
         */
        validateForm(e) {
            showPreloader();            
            var form = e.target;

            var body = {}
            $(form).serializeArray().map(field => {
                body[field.name] = field.value;
            });

            // Making the post request
            this.$http.post(form.action, body).then((response) => {                                
                
                // Displaying the notification
                this.notificationMessage = 'Actie succesvol';
                this.notificationShow = 1;
                this.notificationType = 'success';

                // Recreate the list
                this.receiveJson();

                // Go back to the list
                this.backToList();
                this.resetForm('newPost');                
                hidePreloader();

            }, response => {

                // Displaying the error messages
                $('.validateError').remove();
                this.displayErrorMessages(form, response.json());
                hidePreloader();
            });
        },

        /**
         * Displaying validation errors
         */
        displayErrorMessages(form, jsonResponse) {
            _.forIn(jsonResponse, (error, name) => {                            
                let helpBlock = '<span class="validateError help-block" style="color:red;">' + '<strong>' + error + '</strong>' + '</span>';
                $('#' + form.id + ' #' + name).after(helpBlock);                
            });
        },

        /**
         * Reset the form after succesfull action
         */
        resetForm(form) {
            this.postId = '';
            this.slug = '';
            this.post_name = '';
            this.post_title = '';
            this.post_content = '';
            this.status = '';
            $('.validateError').remove();
            document.getElementById(form).reset();
        },

        backToList() {
            this.resetForm('newPost');
            this.displayForm = 0;
            this.displayList = 1;
            this.displayNewButton = 1;
        },

        displayNewObject() {
            this.displayForm = 1;
            this.displayList = 0;
            this.displayNewButton = 0;
            this.notificationShow = 0;
        }
    },
}

/**
 * Sanitize slug of page
 */
Vue.directive('url', {
  twoWay: true, // this transformation applies back to the vm
  bind: function () {
    this.handler = function () {
        this.el.value = sanitizeUrl(this.el.value);
        this.set(this.el.value);
    }.bind(this);
    this.el.addEventListener('input', this.handler);
  },
  unbind: function () {
    this.el.removeEventListener('input', this.handler);
  }
});

Vue.filter('url', function (str) {
    return sanitizeUrl(str);
})

Vue.http.headers.common['X-CSRF-TOKEN'] = $('meta[name=_token]').attr('content');

/**
 * Sanitizing URL
 */
function sanitizeUrl (str) {
    str = str.toLowerCase();

    // remove accents, swap ñ for n, etc
    var from = "ãàáäâẽèéëêìíïîõòóöôùúüûñç·/_,:;";
    var to   = "aaaaaeeeeeiiiiooooouuuunc------";
    for (var i=0, l=from.length ; i<l ; i++) {
    str = str.replace(new RegExp(from.charAt(i), 'g'), to.charAt(i));
    }

    str = str.replace(/[^a-z0-9 -]/g, '') // remove invalid chars
    .replace(/\s+/g, '-') // collapse whitespace and replace by -
    .replace(/-+/g, '-'); // collapse dashes

    return str;
}

/**
 * Showing the preloader
 */
function showPreloader () {
    $('.spinner').fadeIn(500);
}

/**
 * Hiding the preloader
 */
function hidePreloader () {
    $('.spinner').fadeOut(500);
}
</script>
 
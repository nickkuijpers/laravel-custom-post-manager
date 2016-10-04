<template>
  <form action="{{url}}" class="dropzone" id="{{id}}"></form>
</template>

<script>
  var Dropzone = require('../../vendor/dropzone')
  Dropzone.autoDiscover = false
  export default {
    props: {
      id: {
        type: String,
        required: true
      },
      url: {
        type: String,
        required: true
      },
      clickable: {
        type: Boolean,
        default: true
      },
      acceptedFileTypes: {
        type: String
      },
      thumbnailHeight: {
        type: Number,
        default: 100
      },
      thumbnailWidth: {
        type: Number,
        default: 100
      },
      showRemoveLink: {
        type: Boolean,
        default: true
      },
      maxFileSizeInMB: {
        type: Number,
        default: 4
      },
      maxNumberOfFiles: {
        type: Number,
        default: 50
      },
      autoProcessQueue: {
        type: Boolean,
        default: true
      },
      useCustomDropzoneOptions: {
        type: Boolean,
        default: false
      },
      dropzoneOptions: {
        type: Object
      }
    },
    events: {
      removeAllFiles: function () {
        this.dropzone.removeAllFiles(true)
      },
      processQueue: function () {
        this.dropzone.processQueue()
      }
    },
    ready () {
      var element = document.getElementById(this.id)
      if (!this.useCustomDropzoneOptions) {
        this.dropzone = new Dropzone(element, {
          clickable: this.clickable,
          thumbnailWidth: this.thumbnailWidth,
          thumbnailHeight: this.thumbnailHeight,
          maxFiles: this.maxNumberOfFiles,
          maxFilesize: this.maxFileSizeInMB,
          dictRemoveFile: 'Remove',
          addRemoveLinks: this.showRemoveLink,
          acceptedFiles: this.acceptedFileTypes,
          autoProcessQueue: this.autoProcessQueue,
          dictDefaultMessage: '<i class="material-icons">Sleep bestanden hiernaar toe om te uploaden</i>',
          previewTemplate: '<div class="dz-preview dz-file-preview">  <div class="dz-image" style="width:' + this.thumbnailWidth + 'px;height:' + this.thumbnailHeight + 'px"><img data-dz-thumbnail /></div>  <div class="dz-details">    <div class="dz-size"><span data-dz-size></span></div>    <div class="dz-filename"><span data-dz-name></span></div>  </div>  <div class="dz-progress"><span class="dz-upload" data-dz-uploadprogress></span></div>  <div class="dz-error-message"><span data-dz-errormessage></span></div>  <div class="dz-success-mark"> <i class="material-icons">done</i> </div>  <div class="dz-error-mark"><i class="material-icons">error</i></div></div>'
        })
      } else {
        this.dropzone = new Dropzone(element, this.dropzoneOptions)
      }
      // Handle the dropzone events
      var vm = this
      this.dropzone.on('addedfile', function (file) {
        vm.$parent.$emit('vdropzone-fileAdded', file)
      })
      this.dropzone.on('removedfile', function (file) {
        vm.$parent.$emit('vdropzone-removedFile', file)
      })
      this.dropzone.on('success', function (file, response) {
        let returnObject = {
            'file': file,
            'response': response
        };
        vm.$parent.$emit('vdropzone-success', returnObject)
      })
      this.dropzone.on('error', function (file) {
        vm.$parent.$emit('vdropzone-error', file)
      })
    }
  }
</script>

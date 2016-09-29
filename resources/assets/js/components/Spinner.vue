<style>
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
</style>

<template>
    <div class="spinner">
        <div id="loader"></div>
    </div>
</template>

<script>
export default {
    ready () {

    }
}
</script>

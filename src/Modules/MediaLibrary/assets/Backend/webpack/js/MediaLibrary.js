import Dropzone from 'dropzone'

window.Dropzone = Dropzone

Dropzone.options.ml = {
  paramName: 'media_item[vich][file]'
}

window.addEventListener('DOMContentLoaded', function () {
  Dropzone.discover()
})

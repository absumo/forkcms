import Dropzone from 'dropzone'
import Cropper from 'cropperjs'

window.Dropzone = Dropzone
window.Cropper = Cropper

Dropzone.options.ml = {
  paramName: 'media_item[vich][file]'
}

window.addEventListener('DOMContentLoaded', function () {
  Dropzone.discover()

  const img = document.querySelector('img[data-crop]')
  if (img) {
    img.cropper = new Cropper(img, {})
    const form = img.closest('form')
    if (form) {
      form.addEventListener('submit', (ev) => {
        if (form.dataset.submit === 'true') {
          return
        }
        ev.preventDefault()
        img.cropper.getCroppedCanvas().toBlob((blob) => {
          const file = new File([blob], img.dataset.name, { type: img.dataset.mime, lastModified: new Date().getTime() })
          const container = new window.DataTransfer()
          container.items.add(file)
          form.querySelector('input[type="file"]').files = container.files
          form.dataset.submit = 'true'
          form.requestSubmit(ev.submitter)
        }, img.dataset.mime)
      })
    }
  }
})

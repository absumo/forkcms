import Dropzone from 'dropzone'
import Cropper from 'cropperjs'
import { Copy } from './Copy'
import { Delete } from './Delete'

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
          const file = new window.File([blob], img.dataset.name, { type: img.dataset.mime, lastModified: new Date().getTime() })
          const container = new window.DataTransfer()
          container.items.add(file)
          form.querySelector('input[type="file"]').files = container.files
          form.dataset.submit = 'true'
          form.requestSubmit(ev.submitter)
        }, img.dataset.mime)
      })
    }
  }

  for (const element of document.querySelectorAll('[data-copy]')) {
    element.copy = new Copy(element)
  }

  for (const element of document.querySelectorAll('[data-delete]')) {
    element.delete = new Delete(element)
  }

  const deleteModal = document.getElementById('deleteMediaItemModal')
  if (deleteModal) {
    deleteModal.addEventListener('show.bs.modal', event => {
      const modalBody = event.target.querySelector('.modal-body')
      modalBody.innerHTML = modalBody.innerHTML.replace(/".+"/, '"' + event.relatedTarget.dataset.title + '"')
      event.target.querySelector('#action_id').value = event.relatedTarget.dataset.id
    })
  }
})

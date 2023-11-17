import { Messages } from '../../../../../../Core/assets/js/Components/Messages'

export class Copy {
  constructor (element) {
    const text = element.getAttribute('data-copy')
    element = element.querySelector('a') ?? element
    element.addEventListener('click', (event) => {
      if (navigator.clipboard) {
        event.preventDefault()
        navigator.clipboard.writeText(window.location.origin + text)
        Messages.add('success', window.backend.locale.msg('CopiedLinkToClipboard'))
      }
    })
  }
}

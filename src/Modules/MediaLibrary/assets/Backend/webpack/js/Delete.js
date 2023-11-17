export class Delete {
  constructor (element) {
    element = element.querySelector('a')
    if (element) {
      element.addEventListener('click', (event) => {
        event.preventDefault()
      })
    }
  }
}

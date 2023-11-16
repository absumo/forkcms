<template>
  <div class="media-selector">
    <p class="form-label" v-if="label">{{ label }} <span v-if="limit">(maximum {{ limit }})</span>:</p>
    <div class="sortable-row">
      <Sortable
          :list="images"
          item-key="id"
          :options="{ animation: 150 }"
          @end="moveItemInArray"
      >
        <template #item="{element, index}" v-if="images.length > 0">
          <div class="col-6 col-md-auto draggable">
            <div class="media-selector--preview">
              <a href="#" class="media-selector--remove-btn" @click.prevent="removeSelection(element)" v-if="!min"><i class="fa fa-times"></i></a>
              <img class="" alt="" :src="element.url" @click="openImageModal" draggable="false">
            </div>
            {{ element.id }}
            {{ index }}
          </div>
        </template>
      </Sortable>
      <div class="col-6 col-md-auto" v-if="images.length === 0">
        <div class="media-selector--placeholder" @click="openImageModal"></div>
      </div>
    </div>

    <!--  file browser modal-->
    <div class="modal fade" :id="`fileBrowserModal_${id}`" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-xl">
        <div class="modal-content">
          <div class="modal-header pb-3">
            <h1 class="modal-title fs-5" id="exampleModalLabel" v-if="isFile">Select your files <span v-if="limit">(maximum {{ limit }})</span></h1>
            <h1 class="modal-title fs-5" id="exampleModalLabel" v-else>Select your images <span v-if="limit">(maximum {{ limit }})</span></h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" @click="cancelSelection"></button>
          </div>
          <div class="modal-body">
            <div class="row g-3 media-selector--library">
              <div class="col-lg-2">
                <ul class="file-tree list-unstyled">
                  <FileTreeItem :selected="selectedFolder.id" :item="item" v-for="item in imageOptions" @updateSelectedFolder="updateSelectedFolder" :key="item.id"></FileTreeItem>
                </ul>
              </div>
              <div class="col">
                <div class="row">
                  <div :class="['col-12 media-selector--library-item', {selected: images.includes(image)}, {disabled: limit && limit > 1 && images.length >= limit}]" v-for="image in selectedFolder.items" @click="toggleSelection(image)" :key="image.id">
                    <div class="row align-items-center">
                      <div class="col-auto">
                        <a href="#" @click.prevent="" class="btn btn-primary text-capitalize">
                          <template v-if="images.includes(image)">{{ trans.deselect }}</template>
                          <template v-else="images.includes(image)">{{ trans.select }}</template>
                        </a>
                      </div>
                      <div class="col-auto">
                        <img :src="image.url" alt="" class="" draggable="false">
                      </div>
                      <div class="col">
                        {{ image.title }}
                      </div>
                      <div class="col-auto">
                        <div class="media-selector--used">
                          {{ image.used }}
                        </div>
                      </div>
                      <div class="col-auto">
                        <div class="media-selector--created">
                           {{ image.created }}
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="image-selection mt-4 pt-4 border-top" v-show="images.length && multiple">
              <h2 class="fs-5">Selection:</h2>
              <div class="row gy-2 gx-3">
                <div class="col-md-6 col-lg-4" v-for="item in images">
                  <div class="border rounded p-2 mb-2">
                    <div class="row align-items-center">
                      <div class="col-auto">
                        <img :src="item.url" alt="">
                      </div>
                      <div class="col">
                        {{ item.title }}
                      </div>
                      <div class="col-auto">
                        <div class="media-selector--remove-btn position-static" @click="removeSelection(item)"><i class="fa fa-times"></i></div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary text-capitalize" data-bs-dismiss="modal" @click="cancelSelection">{{ trans.cancel }}</button>
            <button type="button" class="btn btn-primary text-capitalize" @click="saveSelection" :disabled="min && this.images.length < min">{{ trans.save }}</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'
import FileTreeItem from './FileTreeItem.vue'
import { Sortable } from "sortablejs-vue3";

export default {
  props: {
    id: String,
    multiple: Boolean,
    max: {
      type: Number
    },
    min: {
      type: Number
    },
    isFile: Boolean,
    selection: {
      type: Array,
      default: []
    },
    label: String
  },
  components: { FileTreeItem, Sortable },
  data() {
    return {
      images: [],
      fileModal: undefined,
      limit: 1,
      previousSelection: '',
      selectedFolder: {},
      trans: {
        select: 'select',
        deselect: 'deselect',
        cancel: 'cancel',
        save: 'save'
      },
      imageOptions: {
        folder1: {
          name: 'default',
          id: 'folder1',
          items: [
            {
              url: 'https://plus.unsplash.com/premium_photo-1692872337283-4fae5a90fb05?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1374&q=80',
              id: 'image1',
              title: 'Image 1',
              created: '14/08/2023',
              used: 1
            },
            {
              url: 'https://plus.unsplash.com/premium_photo-1692872337283-4fae5a90fb05?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1374&q=80',
              id: 'image2',
              title: 'Image 2',
              created: '26/05/2023',
              used: 1
            },
            {
              url: 'https://plus.unsplash.com/premium_photo-1692872337283-4fae5a90fb05?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1374&q=80',
              id: 'image3',
              title: 'Image 3',
              created: '02/12/2021',
              used: 0
            },
            {
              url: 'https://plus.unsplash.com/premium_photo-1692872337283-4fae5a90fb05?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1374&q=80',
              id: 'image4',
              title: 'Image 4',
              created: '12/09/2023',
              used: 2995
            },
          ],
          children: {
            folder1a: {
              name: 'icons',
              id: 'folder1a',
              items: [
                {
                  url: 'https://plus.unsplash.com/premium_photo-1677094766815-e0fe790e364a?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1287&q=80',
                  id: 'image5',
                  title: 'Image 5',
                  created: '12/09/2023',
                  used: 2995
                },
                {
                  url: 'https://plus.unsplash.com/premium_photo-1677094766815-e0fe790e364a?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1287&q=80',
                  id: 'image6',
                  title: 'Langere image title die heel lang is',
                  created: '12/09/2023',
                  used: 2995
                },
                {
                  url: 'https://plus.unsplash.com/premium_photo-1677094766815-e0fe790e364a?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1287&q=80',
                  id: 'image7',
                  title: 'Image 7',
                  created: '12/09/2023',
                  used: 2995
                },
                {
                  url: 'https://plus.unsplash.com/premium_photo-1677094766815-e0fe790e364a?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1287&q=80',
                  id: 'image8',
                  title: 'Image 8',
                  created: '12/09/2023',
                  used: 2995
                },
              ]
            }
          }
        },
        folder3: {
          name: 'images',
          id: 'folder3',
          items: [
            {
              url: 'https://images.unsplash.com/photo-1501183007986-d0d080b147f9?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1287&q=80',
              id: 'image9',
              title: 'Image 9',
              created: '12/09/2023',
              used: 2995
            },
            {
              url: 'https://images.unsplash.com/photo-1501183007986-d0d080b147f9?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1287&q=80',
              id: 'image10',
              title: 'Image 10',
              created: '12/09/2023',
              used: 2995
            },
            {
              url: 'https://images.unsplash.com/photo-1501183007986-d0d080b147f9?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1287&q=80',
              id: 'image11',
              title: 'Image 11',
              created: '12/09/2023',
              used: 2995
            },
            {
              url: 'https://images.unsplash.com/photo-1501183007986-d0d080b147f9?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1287&q=80',
              id: 'image12',
              title: 'Image 12',
              created: '12/09/2023',
              used: 2995
            },
          ],
          children: {
            folder4: {
              name: 'marketing',
              id: 'folder4',
              items: [
                {
                  url: 'https://images.unsplash.com/photo-1532767153582-b1a0e5145009?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1287&q=80',
                  id: 'image13',
                  title: 'Image 13',
                  created: '12/09/2023',
                  used: 2995
                },
                {
                  url: 'https://images.unsplash.com/photo-1532767153582-b1a0e5145009?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1287&q=80',
                  id: 'image14',
                  title: 'Image 14',
                  created: '12/09/2023',
                  used: 2995
                },
                {
                  url: 'https://images.unsplash.com/photo-1532767153582-b1a0e5145009?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1287&q=80',
                  id: 'image15',
                  title: 'Image 15',
                  created: '12/09/2023',
                  used: 2995
                },
                {
                  url: 'https://images.unsplash.com/photo-1532767153582-b1a0e5145009?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1287&q=80',
                  id: 'image16',
                  title: 'Image 16',
                  created: '12/09/2023',
                  used: 2995
                },
              ],
              children: {
                folder5: {
                  name: 'socials',
                  id: 'folder5',
                  items: [
                    {
                      url: 'https://images.unsplash.com/photo-1525498128493-380d1990a112?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1335&q=80',
                      id: 'image17',
                      title: 'Image 17',
                      created: '12/09/2023',
                      used: 2995
                    },
                    {
                      url: 'https://images.unsplash.com/photo-1525498128493-380d1990a112?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1335&q=80',
                      id: 'image18',
                      title: 'Image 18',
                      created: '12/09/2023',
                      used: 2995
                    },
                    {
                      url: 'https://images.unsplash.com/photo-1525498128493-380d1990a112?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1335&q=80',
                      id: 'image19',
                      title: 'Image 19',
                      created: '12/09/2023',
                      used: 2995
                    },
                    {
                      url: 'https://images.unsplash.com/photo-1525498128493-380d1990a112?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1335&q=80',
                      id: 'image20',
                      title: 'Image 20',
                      created: '12/09/2023',
                      used: 2995
                    },
                  ]
                }
              }
            }
          }
        }
      }
    }
  },
  methods: {
    openImageModal() {
      this.previousSelection = [...this.images]
      this.fileModal.show()
    },
    removeSelection(img) {
      let imageIndex = this.images.findIndex(image => image.id === img.id)
      console.log('index: ', imageIndex)
      console.log('image: ', img)
      if (imageIndex >= 0) this.images.splice(imageIndex, 1)
    },
    toggleSelection(img) {
      let imageIndex = this.images.findIndex(image => image.id === img.id)
      console.log('index: ', imageIndex)
      console.log('image: ', img)
      if (this.limit === 1 && imageIndex < 0) {
        this.images = [img]
        this.saveSelection()
        return
      }
      if (imageIndex >= 0) this.images.splice(imageIndex, 1)
      else if (this.limit && this.images.length >= this.limit) return
      else {
        this.images.push(img)
      }
    },
    cancelSelection() {
      this.images = this.previousSelection
    },
    saveSelection() {
      let ids = this.images.map(image => image.id)
      // TODO: add order to items based on index
      console.log('ids: ', ids)
      // axios.post(url, ids)
      //     .then(response => {
      //
      //     })
      this.fileModal.hide()
    },
    updateSelectedFolder(id) {
      this.selectedFolder = id
      console.log(id)
    },
    moveItemInArray(event) {
      const item = this.images.splice(event.oldIndex, 1)[0]
      this.images.splice(event.newIndex, 0, item)
    }
    // getImages() {
      // get images from media library
      // const url = ''
      // axios.get(url)
      //     .then(response => {
      //
      //     }),
    // getFiles() {
    // get files from media library
    // const url = ''
    // axios.get(url)
    //     .then(response => {
    //
    //     })
  },
  mounted () {
    console.log(window.backend.locale)
    this.trans.select = window.backend.locale.get('lbl', 'Select')
    this.trans.deselect = window.backend.locale.get('lbl', 'Deselect')
    this.trans.cancel = window.backend.locale.get('lbl', 'Cancel')
    this.trans.save = window.backend.locale.get('lbl', 'Save')
    this.fileModal = new bootstrap.Modal(`#fileBrowserModal_${this.id}`, {})
    // this.isFile ? this.getFiles() : this.getImages
    if (this.selection.length) {
      this.images = this.selection
    }
    if (this.max) this.limit = this.max
    else if (this.multiple && !this.max) this.limit = null
    this.selectedFolder = Object.entries(this.imageOptions)[0][1]
  }
}
</script>

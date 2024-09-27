<template>
  <div class="media-selector" ref="media-selector">
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
            <div class="media-selector--preview mb-2">
              <a href="#" class="media-selector--remove-btn" @click.prevent="removeSelection(element)" v-if="!min"><i class="fa fa-times"></i></a>
              <img class="" alt="" :src="element.url" @click="openImageModal" draggable="false" v-if="element.mime.includes('image')">
              <div class="media-selector--placeholder media-selector--placeholder-icon" v-else>
                <i class="fa fa-file-pdf" v-if="element.mime.includes('pdf')"></i>
                <i class="fa fa-file-archive" v-else-if="element.mime.includes('zip')"></i>
                <i class="fa fa-file-audio" v-else-if="element.mime.includes('audio')"></i>
                <i class="fa fa-file-video" v-else-if="element.mime.includes('video')"></i>
                <i class="fa fa-file" v-else></i>
              </div>
            </div>
            <p class="media-selector--preview-title"><small>{{ element.title }}</small></p>
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
                          <template v-else>{{ trans.select }}</template>
                        </a>
                      </div>
                      <div class="col-auto">
                        <img :src="image.url" alt="" class="" draggable="false" v-if="image.mime.includes('image')">
                        <i class="fa fa-file-pdf" v-else-if="image.mime.includes('pdf')"></i>
                        <i class="fa fa-file-archive" v-else-if="image.mime.includes('zip')"></i>
                        <i class="fa fa-file-audio" v-else-if="image.mime.includes('audio')"></i>
                        <i class="fa fa-file-video" v-else-if="image.mime.includes('video')"></i>
                        <i class="fa fa-file" v-else></i>
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
      imageOptions: []
    }
  },
  methods: {
    openImageModal() {
      this.previousSelection = [...this.images]
      this.fileModal.show()
    },
    removeSelection(img) {
      let imageIndex = this.images.findIndex(image => image.id === img.id)
      if (imageIndex >= 0) this.images.splice(imageIndex, 1)
    },
    toggleSelection(img) {
      let imageIndex = this.images.findIndex(image => image.id === img.id)
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
      const eventImage = new CustomEvent("image-selection", {
        bubbles: true,
        detail: { images: () => ids },
      });
      this.$refs['media-selector'].dispatchEvent(eventImage)
      this.fileModal.hide()
    },
    updateSelectedFolder(id) {
      this.selectedFolder = id
    },
    moveItemInArray(event) {
      const item = this.images.splice(event.oldIndex, 1)[0]
      this.images.splice(event.newIndex, 0, item)
    },
    getMedia() {
      // get images from media library
      const url = '/private/en/media-library/media-item-find-all'
      axios.get(url)
          .then(response => {
            this.imageOptions = response.data
            if (this.imageOptions.length > 0) this.selectedFolder = this.imageOptions[0]
          })
    }
  },
  mounted () {
    this.getMedia()
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
  }
}
</script>

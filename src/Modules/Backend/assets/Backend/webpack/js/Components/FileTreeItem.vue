<script>
export default {
  name: 'FileTreeItem',
  props: {
    item: Object,
    selected: Boolean
  },
  methods: {
    emitSelectedFolder(item) {
      this.$emit('update-selected-folder', item)
    }
  }
}
</script>

<template>
  <li :class="['file-tree--folder', {selected: selected === item.id}]">
    <div class="title">
      <span class="file-tree--line"></span>
      <i :class="['far fa-folder-open me-2']"></i>
      <i :class="['far fa-folder me-2']"></i>
      <span @click="emitSelectedFolder(item)">{{ item.name }}</span>
    </div>
    <ul v-if="item.children" class="list-unstyled">
      <FileTreeItem v-for="subItem in item.children" :item="subItem" :selected="selected" @updateSelectedFolder="emitSelectedFolder" :key="subItem.id"></FileTreeItem>
    </ul>
  </li>
</template>

<style scoped>

</style>

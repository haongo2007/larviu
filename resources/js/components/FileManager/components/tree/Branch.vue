<template>
    <div>
      <div v-for="(directory, index) in subDirectories" v-bind:key="index" class="text item">
        <p class="unselectable"
           v-bind:class="{'selected': isDirectorySelected(directory.path)}"
           v-on:click="selectDirectory(directory.path)">
            <i class="far"
               v-if="directory.props.hasSubdirectories"
               v-on:click.stop="showSubdirectories(
                    directory.path,
                    directory.props.showSubdirectories
                  )"
               v-bind:class="[arrowState(index)
                ? 'fa-minus-square'
                : 'fa-plus-square'
               ]"></i>
            <i class="fas fa-minus fa-xs" v-else></i>
            {{ directory.basename }}
        </p>
        <transition name="fade-tree">
          <branch v-show="arrowState(index)"
                  v-if="directory.props.hasSubdirectories"
                  v-bind:parent-id="directory.id" style="padding-left:1em">
          </branch>
        </transition>
      </div>
    </div>
</template>

<script>
export default {
  name: 'Branch',
  props: {
    parentId: { type: Number, required: true },
  },
  computed: {
    /**
     * Subdirectories for selected parent
     * @returns {*}
     */
    subDirectories() {
      return this.$store.getters['fm/tree/directories'].filter(item => item.parentId === this.parentId);
    },
  },
  methods: {
    /**
     * Check, is this directory selected?
     * @param path
     * @returns {boolean}
     */
    isDirectorySelected(path) {
      return this.$store.state.fm.left.selectedDirectory === path;
    },

    /**
     * Show subdirectories - arrow
     * @returns {boolean}
     * @param index
     */
    arrowState(index) {
      return this.subDirectories[index].props.showSubdirectories;
    },

    /**
     * Show/Hide subdirectories
     * @param path
     * @param showState
     */
    showSubdirectories(path, showState) {
      if (showState) {
        // hide
        this.$store.dispatch('fm/tree/hideSubdirectories', path);
      } else {
        // show
        this.$store.dispatch('fm/tree/showSubdirectories', path);
      }
    },

    /**
     * Load selected directory and show files
     * @param path
     */
    selectDirectory(path) {
      // only if this path not selected
      if (!this.isDirectorySelected(path)) {
        this.$store.dispatch('fm/left/selectDirectory', { path, history: true });
      }
    },
  },
};
</script>
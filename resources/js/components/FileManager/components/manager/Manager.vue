<template>
    <div>
      <el-card class="box-card">
        <div slot="header" class="clearfix">
          <disk-list v-bind:manager="manager"></disk-list>
          <breadcrumb v-bind:manager="manager"></breadcrumb>
        </div>
        <table-view v-if="viewType === 'table'" v-bind:manager="manager"></table-view>
        <grid-view v-else v-bind:manager="manager"></grid-view>
      </el-card>
    </div>
</template>

<script>
// Components
import DiskList from './DiskList.vue';
import Breadcrumb from './Breadcrumb.vue';
import TableView from './TableView.vue';
import GridView from './GridView.vue';

export default {
  name: 'Manager',
  components: {
    DiskList,
    Breadcrumb,
    TableView,
    GridView,
  },
  props: {
    manager: { type: String, required: true },
  },
  computed: {
    /**
     * view type - grid or table
     * @returns {default.computed.viewType|(function())|string}
     */
    viewType() {
      return this.$store.state.fm[this.manager].viewType;
    },
  },
};
</script>
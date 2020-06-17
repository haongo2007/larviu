<template>
  <div class="app-container">
      <FileManager></FileManager>
  </div>
</template>

<script>
import FileManager from '@/components/FileManager';
import { toggleClass } from '@/utils';
import '@/assets/custom-theme/index.css'; // the theme changed version element-ui css
import './assets/all.css';
//import './assets/bootstrap.css';

export default {
  name: 'storage',
  components: { FileManager },
  data() {
    return {
      theme: false,
      tags: [
        { name: 'Tag One', type: '' },
        { name: 'Tag Two', type: 'info' },
        { name: 'Tag Three', type: 'success' },
        { name: 'Tag Four', type: 'warning' },
        { name: 'Tag Five', type: 'danger' },
      ],
      slideValue: 50,
      radio: 3,
    };
  },
  watch: {
    theme() {
      toggleClass(document.body, 'custom-theme');
    },
  },
  created() {
    console.log(this.$store.state);
  },
};
</script>

<style scoped>
.field-label{
  vertical-align: middle;
}

.box-card {
  width: 400px;
  max-width: 100%;
  margin: 20px auto;
}

.block {
  padding: 30px 24px;
}

.tag-item {
  margin-right: 15px;
}
</style>

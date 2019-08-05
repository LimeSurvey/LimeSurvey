<template>
  <div class="scoped-bordermecrazy" :class="'col-xs-'+cols">
    <div class="ls-flex ls-flex-row align-items-flex-end align-content-flex-end ls-space margin bottom-10">
      <div class="btn-group" role="group">
        <button type="button" class="btn" @click="fileviz='tablerep'" :class="fileviz=='tablerep' ? 'btn-info active' : 'btn-default'">Table</button>
        <button type="button" class="btn" @click="fileviz='iconrep'" :class="fileviz=='iconrep' ? 'btn-info active' : 'btn-default'">Icons</button>
      </div>
    </div>
    <component :is="fileviz"></component>
    <modals-container />
  </div>
</template>

<script>
import Tablerep from './subcomponents/_tableRepresentation';
import Iconrep from './subcomponents/_iconRepresentation';

export default {
  name: 'FileList',
  components: {
    Tablerep,
    Iconrep
  },
  props: {
    cols: {type: Number, default: 6}
  },
  data() {
    return {
      fileviz: 'tablerep',
    };
  }
}
</script>

<style lang="scss" scoped>
  .scoped-bordermecrazy {
    border-left: 1px solid grey;
  }
</style>

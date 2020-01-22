<script>
import Sidemenu from "./subcomponents/_submenu.vue";
import LoaderWidget from "../helperComponents/loader";

export default {
  name: "GlobalSidemenu",
  components: {
    Sidemenu,
    LoaderWidget
  },
  data() {
    return {
      activeMenuIndex: 0,
      initialPos: { x: 0, y: 0 },
      isMouseDown: false,
      isMouseDownTimeOut: null
    };
  },
  computed: {
    getWindowHeight() {
      return $(document).height();
    },
    calculateSideBarMenuHeight() {
      return $("#pjax-content").height();
    },
    sideBarWidth: {
      get() {
        return this.$store.state.sidebarwidth;
      },
      set(newVal) {
        this.$store.commit("setSidebarwidth", newVal);
      }
    },
    currentMenue() {
      return this.$store.state.menu || [];
    }
  },
  methods: {
    controlActiveLink() {
      //Check for corresponding menuItem
      let currentUrl = window.location.href;
      let lastMenuItemObject = false;
      LS.ld.each(this.currentMenue.entries, (itmm, j) => {
        lastMenuItemObject = LS.ld.endsWith(currentUrl, itmm.partial.split('/').pop())
          ? itmm
          : lastMenuItemObject;
      });

      if(lastMenuItemObject === false) {
        lastMenuItemObject = {partial: 'redundant/_generaloptions_panel'};
      }
      this.$store.commit("setLastMenuItemOpen", lastMenuItemObject.partial.split('/').pop());
    },
    mousedown(e) {
      this.isMouseDown = true;
      $("#sidebar").removeClass("transition-animate-width");
      $("#pjax-content").removeClass("transition-animate-width");
    },
    mouseup(e) {
      if (this.isMouseDown) {
        this.isMouseDown = false;
        this.$store.state.isCollapsed = false;
        if (parseInt(this.sideBarWidth) < 250) {
          this.sideBarWidth = 250;
        }
        $("#sidebar").addClass("transition-animate-width");
        $("#pjax-content").removeClass("transition-animate-width");
      }
    },
    mouseleave(e) {
      if (this.isMouseDown) {
        const self = this;
        this.isMouseDownTimeOut = setTimeout(() => {
          self.mouseup(e);
        }, 1000);
      }
    },
    mousemove(e, self) {
      if (this.isMouseDown) {
        // prevent to emit unwanted value on dragend
        if (e.screenX === 0 && e.screenY === 0) {
          return;
        }
        if (e.clientX > screen.width / 2) {
          self.sideBarWidth = screen.width / 2;
          return;
        }
        self.sideBarWidth = e.pageX + 8 + "px";
        window.clearTimeout(self.isMouseDownTimeOut);
        self.isMouseDownTimeOut = null;
      }
    }
  },
  created() {
    this.$store.dispatch("getMenus").then(
      () => {
        this.controlActiveLink();
    });
  },
  mounted() {
    const self = this;
    $("body").on("mousemove", event => {
        self.mousemove(event, self);
    });
  }
};
</script>


<template>
  <div
    id="sidebar"
    class="ls-flex ls-ba ls-space padding left-0 col-md-4 hidden-xs nofloat transition-animate-width"
    :style="{'height': '100%', 'min-width': '250px', width: sideBarWidth }"
    @mouseleave="mouseleave"
    @mouseup="mouseup"
  >
    <div class="col-12 fill-height ls-space padding all-0" style="height: 100%">
      <div class="mainMenu container-fluid col-12 ls-space padding right-0 fill-height">
        <sidemenu :menu="currentMenue" :style="{'min-height': calculateSideBarMenuHeight}"></sidemenu>
      </div>
    </div>
    <div
      class="resize-handle ls-flex-column"
      :style="{'height': '100%', 'max-height': getWindowHeight}"
    >
      <button
        v-show="!$store.state.isCollapsed"
        class="btn btn-default"
        @mousedown="mousedown"
        @click.prevent="()=>{return false;}"
      >
        <i class="fa fa-ellipsis-v"></i>
      </button>
    </div>
  </div>
</template>


<style lang="scss" scoped>
.resize-handle {
  position: absolute;
  right: 14px;
  top: 0;
  bottom: 0;
  height: 100%;
  width: 4px;
  cursor: col-resize;
  button {
    outline: 0;
    &:focus,
    &:active,
    &:hover {
      outline: 0 !important;
    }
    cursor: col-resize;
    width: 100%;
    height: 100%;
    text-align: left;
    border-radius: 0;
    padding: 0px 7px 0px 4px;
    i {
      font-size: 12px;
      width: 5px;
    }
  }
  .dragPointer {
      cursor: move;
  }
}
</style>

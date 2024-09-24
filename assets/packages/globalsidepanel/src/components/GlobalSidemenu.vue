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

      if (lastMenuItemObject === false) {
        lastMenuItemObject = { partial: 'redundant/_generaloptions_panel' };
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
        self.sideBarWidth = e.pageX - 4 + "px";
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
    class="d-flex col-lg-4 ls-ba position-relative transition-animate-width bg-white py-4 h-100"
    :style="{'min-width': '250px', width: sideBarWidth }"
    @mouseleave="mouseleave"
    @mouseup="mouseup"
  >
    <div class="col-12">
      <div class="mainMenu col-12 ">
        <sidemenu :menu="currentMenue" :style="{ 'min-height': calculateSideBarMenuHeight }"></sidemenu>
      </div>
    </div>
    <div class="resize-handle ls-flex-column" 
         :style="{ 'height': '100%', 'max-height': getWindowHeight }">
      <button 
        v-show="!$store.state.isCollapsed" class="btn " 
        @mousedown="mousedown"
        @click.prevent="() => { return false; }">
        <svg width="9" height="14" viewBox="0 0 9 14" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path fill-rule="evenodd" clip-rule="evenodd"
            d="M0.4646 0.125H3.24762V2.625H0.4646V0.125ZM6.03064 0.125H8.81366V2.625H6.03064V0.125ZM0.4646 5.75H3.24762V8.25H0.4646V5.75ZM6.03064 5.75H8.81366V8.25H6.03064V5.75ZM0.4646 11.375H3.24762V13.875H0.4646V11.375ZM6.03064 11.375H8.81366V13.875H6.03064V11.375Z"
            fill="currentColor" />
        </svg>
      </button>
    </div>
    <!-- this is used for fixing resize handler bug -->
    <div v-if="isMouseDown" class="mouseup-support" style="position:fixed; inset: 0;" />
  </div>
</template>

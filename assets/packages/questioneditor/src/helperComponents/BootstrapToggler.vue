<template>
    <div :id="this.id+'_container'" :class="this.class">
        <input :id="this.id" type="checkbox" />
    </div>
</template>

<script>
import merge from "lodash/merge";
if (!jQuery().bootstrapToggle) {
    require("bootstrap-toggle");
}
const defaults = {};
export default {
    defaults,
    props: {
        value: { type: Boolean, required: true },
        id: { type: String, default: "" },
        class: { type: String, default: "" },
        options: {
            type: Object,
            default: () => ({})
        },
        disabled: {
            type: Boolean,
            default: false
        },
        destroyer: {
            type: Boolean,
            default: false
        }
    },
    data() {
        return { updating: false };
    },
    computed: {
        $$el() {
            return jQuery('#'+this.id);
        }
    },
    watch: {
        value(newValue) {
            if (this.updating) {
                return;
            }
            this.$$el.bootstrapToggle(newValue ? "on" : "off");
        },
        disabled(newValue) {
            this.$$el.bootstrapToggle(newValue ? "disable" : "enable");
        },
        destroyer(nV) {
            if (!nV) {
                this.$destroy();
            }
        }
    },
    mounted() {
        if (this.value) {
            this.$$el[0].checked = true;
        }
        this.$$el.bootstrapToggle(merge(defaults, this.options));
        if (this.disabled) {
            this.$$el.bootstrapToggle("disable");
        }
        this.$$el.change(() => {
            this.updating = true;
            this.$emit("input", this.$$el.prop("checked"));
            this.$nextTick(() => (this.updating = false));
        });
    },
    beforeDestroy() {
        this.$$el.bootstrapToggle("destroy");
        this.$$el.off("change");
    }
};
</script>

<style src="bootstrap-toggle/css/bootstrap-toggle.css" />

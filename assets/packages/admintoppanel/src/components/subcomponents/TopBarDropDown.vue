<template>
    <div class="topbardropdown">
        <button-element v-if="mainButton" :button="mainButton" @click.native.stop="handleClick" />
        <ul
            v-if="isOpen && list"
            :class="'dropdown-box ' + list.class"
            :aria-labelledby="list.arialabelledby"
        >
            <li v-for="item in list.items" :key="item.id">
                <link-element
                    v-if="isActive"
                    :active="isActive"
                    :item="item"
                    @click="handleLinkClick()"
                />
                <link-element v-else :item="item" @click="handleLinkClick()" />
                <seperator-element
                    v-if="item.class === 'divider' && item.role === 'seperator'"
                    :item="item"
                />
                <dropdown-header-element v-if="item.class === 'dropdown-header'" :item="item" />
            </li>
        </ul>
    </div>
</template>
<script>
import Button from "./TopBarButton.vue";
import Link from "./TopBarLink.vue";
import Seperator from "./Seperator.vue";
import DropDownHeader from "./DropDownHeader.vue";

// TODO: Es wird auch bei einem Seperator oder DropDownHeader Element ein Link-Element mitgerendert.
// TODO: Wie kann man das unterdrücken?

export default {
    name: "TopBarDropDown",
    components: {
        "button-element": Button,
        "link-element": Link,
        "seperator-element": Seperator,
        "dropdown-header-element": DropDownHeader
    },
    props: ["list", "mainButton", "dropdownOpen"],
    data: () => {
        return {
            uniquid: Math.floor((1 + Math.random()) * 1000000),
            isOpen: false,
            isActive: false,
            selectedItem: 0
        };
    },
    methods: {
        toggleOpen() {
            if(this.dropdownOpen) {
                 $("body").trigger("click");
            }
            this.isOpen = !this.isOpen;
            this.$emit('dropdowntrigger', this.isOpen);
            if (this.isOpen) {
                console.log("Dropdown is open");
                $("body").on(
                    "click.topbardropdown" + this.uniquid,
                    ":not(.topbardropdown)",
                    () => {
                        this.isOpen = false;
                        $("body").off("click.topbardropdown" + this.uniquid);
                    }
                );
            } else {
                console.log("Dropdown is closed");
                $("body").off("click.topbardropdown" + this.uniquid);
            }
        },
        handleClick() {
            this.toggleOpen();
        },
        handleLinkClick() {
            this.isActive = true;
        }
    }
};
</script>

<style scoped lang="scss">
$black: #212529;
$white: #ffffff;
$green: #00b248;
$green-active: #66ffa6;

ul {
    list-style-type: none;
    li {
        position: relative;
        margin: 0.2em;
        text-align: left;
    }
}

* {
    box-sizing: border-box;
}

.dropdown-box {
    position: absolute;
    top: 100%;
    left: 0;
    display: block;
    float: left;
    border: 1px solid rgba(0, 0, 0, 0.15);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.175);
    background-color: $white;
    width: auto;
    max-width: 80vw;
}
</style>

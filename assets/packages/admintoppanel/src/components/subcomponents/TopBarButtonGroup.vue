<template>
    <div class="topbarbuttongroup">
        <dropdown-element v-if="list" :list="list" :mainButton="mainButton" />
        <button-element v-else :button="mainButton" />
    </div>
</template>

<script>
import DropDown from "./TopBarDropDown.vue";
import Button from "./TopBarButton.vue";

export default {
    name: "TopBarButtonGroup",
    props: {
        list: Object,
        mainButton: Object
    },
    components: {
        "dropdown-element": DropDown,
        "button-element": Button
    },
    data: () => {
        return {};
    }
};
</script>

<scoped style>

</style>

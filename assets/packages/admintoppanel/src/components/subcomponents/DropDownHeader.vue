<template>
    <div :class="'dropdownheader ' + item.class">{{item.name}}</div>
</template>

<script>
export default {
    name: "DropDownHeader",
    props: {
        item: {
            class: String,
            name: String
        }
    },
    data() {
        return {};
    }
};
</script>

<style lang="scss" scoped>
</style>

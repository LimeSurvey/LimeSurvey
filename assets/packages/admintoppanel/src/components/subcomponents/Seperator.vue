<template>
    <div :class="'seperator ' + item.class" :role="item.role" />
</template>

<script>
export default {
    name: "Seperator",
    props: {
        item: {
            role: String,
            class: String
        }
    },
    data() {
        return {};
    }
};
</script>

<style lang="scss" scoped>
</style>

<script>
export default {
    props: {
        'icon' : {type: String},
        'iconType' : {type: String}
    }
}
</script>

<template>
    <i v-if="iconType == 'fontawesome'" class="fa" :class="'fa-'+icon">&nbsp;</i>
    <img v-else-if="iconType == 'image'" width="32px" :src="icon" />
    <i v-else-if="iconType == 'iconclass'" :class="icon">&nbsp;</i>
    <span v-else></span>
</template>

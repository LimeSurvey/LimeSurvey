<template>
<div :id="'loader-'+id" class=" loader--loaderWidget ls-flex ls-flex-column align-content-center align-items-center"
        style="min-height: 100%;">
        <div class="ls-flex align-content-center align-items-center">
            <div class="loader-adminpanel text-center" :class="extraClass">
                <div class="contain-pulse animate-pulse">
                    <div class="square"></div>
                    <div class="square"></div>
                    <div class="square"></div>
                    <div class="square"></div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
    .loader-adminpanel .contain-pulse .square{
        display: block;
    }
</style>

<script>
export default {
    name: 'loaderWidget',
    props: {
        id: {type: String, default: Math.floor(1000*Math.random())},
        extraClass: {type: String, default: ''}
    },
}
</script>

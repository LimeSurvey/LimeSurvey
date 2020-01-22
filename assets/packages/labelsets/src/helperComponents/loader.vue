<template>
<div class="container-fluid loader--loaderContainer">
    <div :id="'loader-'+id" class="loader--loaderWidget ls-flex ls-flex-column align-content-center align-items-center"
        style="min-height: 100%;">
        <div class="ls-flex align-content-center align-items-center">
            <div class="loader-questioneditor text-center">
                <div class="contain-pulse animate-pulse">
                    <div class="square"></div>
                    <div class="square"></div>
                    <div class="square"></div>
                    <div class="square"></div>
                </div>
            </div>
        </div>
    </div>
</div>
</template>

<style scoped>
    .loader--loaderContainer {
        min-height: 50vh;
        width:100%;
        position: relative;
    }
    .loader--loaderWidget{
        position: absolute;
        top:0;
        left:0;
        width: 100%;
        height: 100%;
        z-index:4000;
        background: hsla(0,0,90,0.4);
    }
</style>

<script>
export default {
    name: 'loaderWidget',
    props: {
        id: {type: String, default: Math.floor(1000*Math.random())}
    },
}
</script>

<template>
    <div
        :id="'loader-'+id"
        class="loader--loaderWidget ls-flex ls-flex-column align-content-center align-items-center"
        style="min-height: 100%;"
    >
        <div class="ls-flex align-content-center align-items-center">
            <div class="loader-toppanel text-center">
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
.loader--loaderWidget {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 3em;
}
</style>

<script>
export default {
    name: "loaderWidget",
    props: {
        id: { type: String, default: 'rnd-'+Math.floor(1000 * Math.random()) }
    }
};
</script>

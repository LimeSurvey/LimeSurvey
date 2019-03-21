<template>
    <div style="overflow:scroll;max-height:4rem;"><pre>{{ debug }}</pre></div>
</template>

<script>
export default {
    name: 'stub-set',
    props: {
        debug: {type: [Object, Boolean]}
    }
}
</script>

<style>

</style>

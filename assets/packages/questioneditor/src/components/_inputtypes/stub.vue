<template>
    <div style="overflow:scroll;max-height:4rem;"><pre>{{ debug }}</pre></div>
</template>

<script>

import inputTypeMixin from '../../mixins/inputTypeMixin';
export default {
    name: 'stub-set',
    mixins: [inputTypeMixin],
}
</script>

<style>

</style>

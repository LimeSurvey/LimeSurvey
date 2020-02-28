<template>
    <div style="overflow:scroll;max-height:4rem;"><pre>{{ debug }}</pre></div>
</template>

<script>

import abstractBaseType from '../abstracts/_abstractInputType';
export default {
    name: 'stub-set',
    extends: abstractBaseType,
    /*
    Abstract base provides props: 
     - elId
     - elName
     - elLabel
     - elHelp
     - currentValue
     - elOptions
     - readonly
     - debug
    */
    /*
    Abstract base provides data: 
        - triggerShowHelp

    Abstract base provides computed values: 
        - curValue
        - getClasses
        - showHelp
        - hasPrefix
        - hasSuffix
    */
}
</script>

<style>

</style>

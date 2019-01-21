<script>
export default {
    name: 'GeneralSettings',
    data() {
        return {};
    }
}
</script>

<template>
    <div class="col-sm-4 col-xs-12 scope-border-simple scope-set-min-height">
        <div class="panel panel-default">
            <div class="panel-heading"> {{"General Settings" | translate }}</div>
            <div class="panel-body">
                <div class="list-group">
                    <div class="list-group-item scope-general-setting-block" v-for="generalSetting in $store.state.questionGeneralSettings" :key="generalSetting.name">
                        <label :for="generalSetting.formElementId" class="list-group-item-heading"> {{generalSetting.title}} </label>
                        <div class="list-group-item-text form-row" v-html="generalSetting.formElement" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style lang="scss" scoped>
.scope-general-setting-block {
    margin: 1rem  0.1rem;
}
.scope-set-min-height {
    min-height: 40vh;
}
</style>

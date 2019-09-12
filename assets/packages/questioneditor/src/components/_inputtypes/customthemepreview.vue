<template>
    <div id="customThemePreview">
        <div v-if="showPreview">
            <p><b>Preview Image for Custom Theme: {{ previewImage.title }}</b></p>
            <p>Debug Path: {{ previewImage.preview }}</p>
            <img :src="'../../../../..' + previewImage.preview" alt="Preview image for custom theme" />
        </div>
    </div>
</template>

<script>
export default {
    name: 'CustomThemePreview',
    props: {
        theme: String,
    },
    data() {
        return {
            selectedTheme: String,
            showPreview: false,
            previewImage: {
                title: String,
                preview: String,
            }
        }
    },
    computed: {
        currentSelectedTheme: {
            set(theme) {
                this.selectedTheme = theme;
                this.$store.commit('setCurrentSelectedTheme', theme);
            },
            get() {
                return this.$store.getters.currentSelectedTheme;
            }
        }
    },
    methods: {
        getCurrentSelectedTheme() {
            //if (this.theme !== 'core') {
                this.currentSelectedTheme = this.theme;
                console.log('SelectedTheme: ', this.selectedTheme);
            //}
        },
        fetchPreviewImageForCustomTheme() {
            if (this.selectedTheme !== null) {
                this.$store.dispatch('getPreviewImageForCustomTheme').then( (result) => {
                    this.showPreview = true;
                    this.previewImage = result.data[this.theme];
                    console.log('RESULT: ', result);
                }).catch((error) => {
                    this.showPreview = false;
                    console.log('ERROR: ', error);
                });
            }
        },
    },
    created() {
        this.getCurrentSelectedTheme();
          //this.$store.dispatch('getPreviewImageForCustomTheme').then((result) => {
          //  console.log('RESULT: ', result);
        //}).catch(error => {
          //  console.log('ERROR: ', error);
        //})
    },
    mounted() {
        this.fetchPreviewImageForCustomTheme();
    }
    
}
</script>

<style scoped lang="scss">

</style>
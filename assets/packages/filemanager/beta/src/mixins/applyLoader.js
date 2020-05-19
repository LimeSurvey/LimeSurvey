export default {
    props: {
        loading: {type: Boolean, default: false},
    },
    computed: {
        loadingState: {
            get() {
                return this.loading;
            },
            set(nV) {
                this.$emit("setLoading", nV);
            }
        }
    },
    methods: {
        setLoading(nV) {
            this.$log.log("Loading set on file list component");
            this.loadingState = nV;
        }
    }
};

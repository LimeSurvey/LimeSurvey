
export default {
    methods: {
        translate(sidemenu, string) {
            return sidemenu.translate[string] || string;
        }
    },
   /**  filters: {
        translate ( tring) {
            return this.$store.translate[string] || string;
        }
    }
    */
}
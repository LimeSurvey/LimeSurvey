<script>
import he from 'he';

export default {
    name: "TopBarButton",
    props: ["button"],
    data: () => {
        return {
            isLoading: false
        };
    },
    computed: {
        buttonIcon() {
            if(this.button.isSaveButton && this.isLoading === true) {
                return "fa fa-cog fa-spin ";
            }
            return this.button.icon || "";
        },
        buttonUrl() {
            return this.button.isCloseButton ? this.$store.state.closeButtonUrl : this.button.url;
        }
    },
    methods: {
        clicked(event) {
            this.$log.log('Button clicked -> ', this.button);
            if (this.button.isSaveButton) {
                this.isLoading = true;
                LS.EventBus.$emit("saveButtonCalled", this.button);
                return false;
            }
            return true;
        }
    },
    mounted() {
        LS.EventBus.$on('loadingFinished', () => {
            if(this.isLoading) {
                this.isLoading=false;
            }
        });
    },
    render(h) {
        const button = this.button;

        if(button.class === 'dropdown-toggle') {
            return (
            <div class="topbarbutton">
                <button
                    type="button"
                    class={'btn btn-default navbar-btn button ' + button.class}
                    id={button.id}
                    data-toggle={button.datatoggle}
                    aria-haspopup={button.ariahaspopup}
                    aria-expanded={button.ariaexpanded}
                    onClick={this.clicked}
                    data-btntype="2"
                >
                    <i class={this.buttonIcon + ' icon'} />
                    {he.decode("&nbsp;")}{he.decode(button.name)}{he.decode("&nbsp;")}
                    <i class={button.iconclass + ' icon'} />
                </button>
            </div>
            )
        } 

        const classAddition = this.buttonIcon.includes('-o') || button.class.includes('btn-danger') || button.class.includes('btn-success')
            ? ' white '
            : ' btn-default ';

        return  (
            <div class="topbarbutton">
                <a
                    type="button"
                    class= {
                        'btn navbar-btn button '
                        + classAddition
                        + button.class
                    }
                    href={this.buttonUrl}
                    id={button.id}
                    data-placement={button.dataplacement || null }
                    data-toggle={button.datatoggle || null }
                    title={button.title || null }
                    target={button.target || null }
                    access-key={button.accesskey || null }
                    data-btntype="1"
                    onClick={this.clicked}
                >
                    { this.buttonIcon != '' && this.isLoading === false
                        ? <i class={this.buttonIcon + ' icon'} />
                        : ''
                    }
                    {he.decode("&nbsp;")}{he.decode(button.name)}{he.decode("&nbsp;")}
                    { button.iconclass !== undefined 
                        ? <i class={'icon ' + button.iconclass} />
                        : ''
                    }
                </a>
            </div>
        )
    }
};
</script>

<style scoped lang="scss">
.icon {
    margin-right: 2px;
}

.white {
    color: white;
}

.btn-danger {
    color: white;
}
</style>

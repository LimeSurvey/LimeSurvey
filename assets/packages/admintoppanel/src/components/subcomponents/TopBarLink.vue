<script>
import he from 'he';
import isArray from 'lodash/isArray';

export default {
    name: "TopBarLink",
    props: {
        item: {type: Object|Array, required: true},
        active: {type: Boolean, default: true},
        preview_title: {type: String, default: ''},
        hasDropdown: {type: Boolean, default: false}
    },
    data() {
        return {};
    },
    render(h) {
        return (
            <div class="topbarlink">
                <a
                    href={this.item.url}
                >
                    {this.item.icon != '' ? <i class={this.item.icon + ' icon'} /> : '' }
                    {he.decode('&nbsp;')}{this.item.name }{he.decode('&nbsp;')}
                    { this.preview_title != '' ? <small><i>{ he.decode(this.preview_title)}</i></small> : ''}
                    { this.hasDropdown 
                        ? <i class="fa fa-chevron-right pull-right" />
                        : ''
                    }
                </a>
            </div>
        );
    }
};
</script>

<style lang="scss" scoped>
.topbarlink a {
    white-space: nowrap;
}

$black: #212529;
$white: #ffffff;

.icon {
    margin-right: 2px;
}

a {
    display: block;
    padding: 0.55em 1em;
    text-decoration: none;
    color: $black;

    &:hover {
        text-decoration: none;
        color: $white;
        background-color: var(--LS-admintheme-hovercolor);
    }

    &.active {
        background-color: var(--LS-admintheme-infocolor);
    }
}
</style>

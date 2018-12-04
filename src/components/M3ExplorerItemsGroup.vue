<template>
    <div class="m3-explorer-items-group">
        <div class="m3-explorer-items-group-name-wrapper">
            <div class="m3-explorer-items-group-name">{{ data.name }}</div>
            <m3-button icon="folder-plus" @click.native.stop="showDropdown" v-if="!data.static"></m3-button>

            <m3-dropdown v-if="!data.static" ref="add-dropdown">
                <div class="m3-form-inline">
                    <div class="m3-form-field"><input maxlength="50" ref="add-input" @keyup.enter="addItem" /></div>
                    <m3-buttons>
                        <m3-button type="success" icon="check" @click.native="addItem"></m3-button>
                        <m3-button type="danger" icon="times" @click.native="hideDropdown"></m3-button>
                    </m3-buttons>
                </div>
            </m3-dropdown>
        </div>

        <div class="m3-explorer-items-wrapper" v-if="staticItems.length || dynamicItems.length">
            <div class="m3-explorer-items" v-if="staticItems.length">
                <m3-explorer-item v-for="item in staticItems"
                    :key="item.id"
                    :collection="staticItems"
                    :data="item"
                    :groupId="data.id"
                    :groupIsStatic="true"
                    :urls="urls"
                    :events="events" />
            </div>

            <div class="m3-explorer-items" v-if="dynamicItems.length">
                <m3-explorer-item v-for="item in dynamicItems"
                    :key="item.id"
                    :collection="dynamicItems"
                    :data="item"
                    :groupId="data.id"
                    :groupIsStatic="data.static"
                    :urls="urls"
                    :events="events" />
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios'
import M3Button from './M3Button'
import M3Buttons from './M3Buttons'
import M3Dropdown from './M3Dropdown'
import M3ExplorerItem from './M3ExplorerItem'

export default {
    components: {
        M3Button,
        M3Buttons,
        M3Dropdown,
        M3ExplorerItem,
    },

    props: {
        data: Object,
        events: Object,
        urls: Object,
    },

    computed: {
        staticItems() {
            return this.data.items.static || []
        },

        dynamicItems() {
            return this.data.items.length ? this.data.items : (this.data.items.dynamic || [])
        }
    },

    created() {
        // listen to events
        this.$root.eventHub.$on('sort-items', this.sortItems)

        // sort all initial items
        this.sortAllItems()
    },

    beforeDestroy() {
        this.$root.eventHub.$off('sort-items', this.sortItems)
    },

    methods: {
        sortItems: function(data) {
            if (data.length) {
                data.sort(function(a, b) {
                    const nameA = a.name.toUpperCase()
                    const nameB = b.name.toUpperCase()

                    if (nameA < nameB) {
                        return -1
                    }
                    if (nameA > nameB) {
                        return 1
                    }

                    return 0
                })
            }

            return data
        },

        sortAllItems() {
            if (this.staticItems.length) this.sortItems(this.staticItems)
            if (this.dynamicItems.length) this.sortItems(this.dynamicItems)
        },

        showDropdown: function(event) {
            const dropdownRef = this.$refs['add-dropdown']

            if (dropdownRef) {
                dropdownRef.setDispatcher(event.currentTarget)
                dropdownRef.show()

                // focus on the input field, but first wait till the dom is updated
                this.$nextTick(() => {
                    const inputRef = this.$refs['add-input']
                    if (inputRef) {
                        inputRef.focus()
                    }
                })
            }
        },

        hideDropdown() {
            const dropdownRef = this.$refs['add-dropdown']
            const inputRef = this.$refs['add-input']

            if (dropdownRef && inputRef) {
                dropdownRef.hide()
                inputRef.value = ''
            }
        },

        addItem() {
            const ref = this.$refs['add-input']

            if (this.urls.addItem && ref && ref.value.trim()) {
                // show loader
                //this.$set(item, 'loading', true)

                // create form data, so we can catch $_POST with PHP for instance...
                const formData = new FormData()
                formData.append('group', this.data.id || 0)
                formData.append('value', ref.value.trim() || '')

                // make request
                axios.post(this.urls.addItem, formData)
                .then((response) => {
                    this.hideDropdown()

                    if (this.data.items.dynamic) {
                        this.data.items.dynamic.push(response.data.data)
                        this.sortItems(this.data.items.dynamic)
                    } else {
                        this.data.items.push(response.data.data)
                        this.sortItems(this.data.items)
                    }
                })
                .catch((error) => {
                    console.error(error)
                })
            }
        },
    }
}
</script>

<style lang="scss">
    .m3-explorer-items-group {
        &:not(:last-child) {
            margin-bottom: 2rem;
        }
    }

    .m3-explorer-items-group-name-wrapper {
        align-items: center;
        display: flex;
        margin-bottom: 0.5rem;

        .m3-dropdown {
            padding: 0.5rem;

            .m3-form-field {
                margin-right: 0.5rem;
            }
        }
    }

    .m3-explorer-items-group-name {
        flex-grow: 1;
        font-family: $font-family-bold;
        font-size: 0.625rem;
        color: #747474;
        height: 1rem;
        letter-spacing: 0.5px;
        line-height: 1.6;
        overflow: hidden;
        margin-right: 0.5rem;
        padding-left: 0.5rem;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .m3-explorer-items {
        &:not(:last-child) {
            border-bottom: 1px solid #e8e8e8;
            margin-bottom: 0.25rem;
            padding-bottom: 0.25rem;
        }
    }
</style>

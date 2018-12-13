<template>
    <div class="m3-explorer-items-group">
        <div class="m3-explorer-items-group-name-wrapper">
            <div class="m3-explorer-items-group-name">{{ data.name }}</div>
            <m3-button type="transparent" icon="folder-plus-dark" size="large" @click="showPopper" v-if="!data.static" flat></m3-button>

            <m3-popper v-if="!data.static" ref="add-popper">
                <div class="m3-form-inline">
                    <div class="m3-form-field"><input maxlength="50" ref="add-input" @keyup.enter="addItem" /></div>
                    <m3-buttons>
                        <m3-button type="success" icon="check-light" @click="addItem" flat></m3-button>
                        <m3-button type="danger" icon="times-light" @click="hidePopper" flat></m3-button>
                    </m3-buttons>
                </div>
            </m3-popper>
        </div>

        <div class="m3-explorer-items-wrapper" v-if="staticItems.length || dynamicItems.length">
            <div class="m3-explorer-items" v-if="staticItems.length">
                <m3-explorer-item v-for="item in staticItems"
                    :key="item.id"
                    :collection="staticItems"
                    :data="item"
                    :groupId="data.id"
                    :groupName="data.name"
                    :groupIsStatic="true"
                    :settings="settings" />
            </div>

            <div class="m3-explorer-items" v-if="dynamicItems.length">
                <m3-explorer-item v-for="item in dynamicItems"
                    :key="item.id"
                    :collection="dynamicItems"
                    :moveOptions="moveOptions"
                    :data="item"
                    :groupId="data.id"
                    :groupName="data.name"
                    :groupIsStatic="data.static"
                    :rootCollection="dynamicItems"
                    :settings="settings" />
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios'
import M3Button from '@mach3builders/m3-components/M3Button'
import M3Buttons from '@mach3builders/m3-components/M3Buttons'
import M3Popper from '@mach3builders/m3-components/M3Popper'
import M3ExplorerItem from './M3ExplorerItem'

export default {
    components: {
        M3Button,
        M3Buttons,
        M3Popper,
        M3ExplorerItem,
    },

    props: {
        data: Object,
        settings: Object,
    },

    computed: {
        staticItems() {
            return this.data.items.static || []
        },

        dynamicItems() {
            return this.data.items.length ? this.data.items : (this.data.items.dynamic || [])
        },

        moveOptions() {
            return !this.data.static ? this.flattenItems(this.dynamicItems) : []
        }
    },

    created() {
        // sort all initial items
        this.sortAllItems()
    },

    methods: {
        flattenItems(items, level) {
            let final = []
            level = level || 1

            if (items && items.length && !items.static) {
                let indent = ''

                for (let i=0; i<level; i++) {
                    indent += '&nbsp;&nbsp;&nbsp;';
                }

                items.forEach((item, index) => {
                    this.$set(item, '_data', {})
                    this.$set(item._data, 'option', indent + item.name)
                    final.push(item)
                    if (typeof item.items !== 'undefined') {
                        final = final.concat(this.flattenItems(item.items, level+1))
                    }
                })
            }

            return final
        },

        sortAllItems() {
            if (this.staticItems.length) this.$root.eventHub.$emit('explorer-items-group:sort-items', this.staticItems)
            if (this.dynamicItems.length) this.$root.eventHub.$emit('explorer-items-group:sort-items', this.dynamicItems)
        },

        showPopper(data) {
            data.event.stopPropagation()
            const popperRef = this.$refs['add-popper']

            if (popperRef) {
                popperRef.setDispatcher(data.dispatcher)
                popperRef.show()

                // focus on the input field, but first wait till the dom is updated
                this.$nextTick(() => {
                    const inputRef = this.$refs['add-input']
                    if (inputRef) {
                        inputRef.focus()
                    }
                })
            }
        },

        hidePopper() {
            const popperRef = this.$refs['add-popper']
            const inputRef = this.$refs['add-input']

            if (popperRef && inputRef) {
                popperRef.hide()
                inputRef.value = ''
            }
        },

        addItem() {
            const ref = this.$refs['add-input']

            if (this.settings.urls.addItem && ref && ref.value.trim()) {
                // show loader
                //this.$set(item, 'loading', true)

                // create form data, so we can catch $_POST with PHP for instance...
                const formData = new FormData()
                formData.append('group', this.data.id || 0)
                formData.append('value', ref.value.trim() || '')

                // make request
                axios.post(this.settings.urls.addItem, formData)
                .then((response) => {
                    if (!response.data.data.items) {
                        this.$set(response.data.data, 'items', [])
                    }

                    if (this.data.items.dynamic) {
                        this.data.items.dynamic.push(response.data.data)
                        this.$root.eventHub.$emit('explorer-items-group:sort-items', this.data.items.dynamic)
                    } else {
                        this.data.items.push(response.data.data)
                        this.$root.eventHub.$emit('explorer-items-group:sort-items', this.data.items)
                    }

                    this.hidePopper()
                })
                .catch((error) => {
                    console.error(error)
                })
            }
        },
    }
}
</script>

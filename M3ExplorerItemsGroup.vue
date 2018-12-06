<template>
    <div class="m3-explorer-items-group">
        <div class="m3-explorer-items-group-name-wrapper">
            <div class="m3-explorer-items-group-name">{{ data.name }}</div>
            <m3-button type="transparent" icon="folder-plus" size="large" @button:clicked="showPopper" v-if="!data.static" flat></m3-button>

            <m3-popper v-if="!data.static" ref="add-popper">
                <div class="m3-form-inline">
                    <div class="m3-form-field"><input maxlength="50" ref="add-input" @keyup.enter="addItem" /></div>
                    <m3-buttons>
                        <m3-button type="success" icon="check" @click.native="addItem" flat></m3-button>
                        <m3-button type="danger" icon="times" @click.native="hidePopper" flat></m3-button>
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
        this.$root.eventHub.$on('explorer-item:added', this.addItem)

        // sort all initial items
        this.sortAllItems()
    },

    beforeDestroy() {
        this.$root.eventHub.$off('sort-items', this.sortItems)
        this.$root.eventHub.$off('explorer-item:added', this.addItem)
    },

    methods: {
        sortItems(data) {
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
                });
            }

            return data
        },

        sortAllItems() {
            if (this.staticItems.length) this.sortItems(this.staticItems)
            if (this.dynamicItems.length) this.sortItems(this.dynamicItems)
        },

        showPopper(vm, event) {
            event.stopPropagation()
            const popperRef = this.$refs['add-popper']

            if (popperRef) {
                popperRef.setDispatcher(vm)
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

        addItem(event, vm, parentData) {
            const ref = vm ? vm.$refs['add-input'] : this.$refs['add-input']
            if (this.urls.addItem && ref && ref.value.trim()) {
                if (!vm || (vm && vm.$parent === this)) {
               if (vm) console.log(vm.$parent)
                    // show loader
                    //this.$set(item, 'loading', true)

                    // create form data, so we can catch $_POST with PHP for instance...
                    const formData = new FormData()
                    formData.append('group', this.data.id || 0)
                    formData.append('parent', parentData ? parentData.id : 0)
                    formData.append('value', ref.value.trim() || '')

                    // make request
                    axios.post(this.urls.addItem, formData)
                    .then((response) => {
                        this.hidePopper()

                        if (this.data.items.dynamic) {
                            if (!parentData) {
                                this.data.items.dynamic.push(response.data.data)
                                this.sortItems(this.data.items.dynamic)
                            } else {
                                if (!parentData.items) {
                                    this.$set(parentData, 'items', [])
                                }

                                parentData.items.push(response.data.data)
                                this.sortItems(parentData.items)
                            }
                        } else {
                            this.data.items.push(response.data.data)
                            this.sortItems(this.data.items)
                        }
                    })
                    .catch((error) => {
                        console.error(error)
                    })
                }
            }
        },
    }
}
</script>

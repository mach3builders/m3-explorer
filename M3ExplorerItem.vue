<template>
    <div class="m3-explorer-item-wrapper">
        <div class="m3-explorer-item">
            <m3-icon :name="expandIcon" v-if="hasItems()" @icon:clicked="toggleItems"></m3-icon>
            <div class="m3-explorer-item-name-wrapper" :class="itemClasses" @click="click">
                <m3-icon :name="icon" size="large"></m3-icon>
                <div class="m3-explorer-item-name" v-if="!renaming">{{ data.name }}</div>
                <div class="m3-explorer-item-input" v-if="renaming" @click.stop>
                    <input maxlength="50" v-model="data.name" @keyup.enter="renamed" ref="input">
                </div>

                <m3-button type="transparent" icon="ellipsis-h" ref="actions-button" v-if="actionsAllowed" @button:clicked="showActionsPopper" flat></m3-button>

                <m3-popper v-if="actionsAllowed" ref="actions-popper" @popper:shown="focusItem" @popper:hidden="blurItem">
                    <ul>
                        <li @click="showAddPopper"><div>{{ settings.text.addItemAction }}</div></li>
                        <li @click="showMovePopper"><div>{{ settings.text.moveItemAction }}</div></li>
                        <li @click="rename"><div>{{ settings.text.renameItemAction }}</div></li>
                        <li @click="removeConfirm">
                            <div>{{ settings.text.removeItemAction }}</div>
                            <div class="m3-explorer-item-remove-confirm" :class="{ 'm3-show': removeConfirmClass }">
                                <m3-buttons>
                                    <m3-button type="danger" icon="times" @button:clicked="removeCancelled" flat></m3-button>
                                    <m3-button type="success" icon="check" @button:clicked="remove" flat></m3-button>
                                </m3-buttons>
                            </div>
                        </li>
                    </ul>
                </m3-popper>

                <m3-popper v-if="!data.static" ref="add-popper" @popper:shown="focusItem" @popper:hidden="blurItem">
                    <div class="m3-form-inline">
                        <div class="m3-form-field"><input maxlength="50" ref="add-input" @keyup.enter="add" /></div>
                        <m3-buttons>
                            <m3-button type="success" icon="check" @button:clicked="add" flat></m3-button>
                            <m3-button type="danger" icon="times" @button:clicked="hideAddPopper" flat></m3-button>
                        </m3-buttons>
                    </div>
                </m3-popper>

                <m3-popper v-if="!data.static" ref="move-popper" @popper:shown="focusItem" @popper:hidden="blurItem">
                    <div class="m3-form-inline">
                        <div class="m3-form-field">
                            <select ref="move-input">
                                <option :value="collectionOption.id" v-for="collectionOption in collectionOptions" :key="collectionOption.id" v-html="collectionOption.option" :selected="isOptionSelected(collectionOption)"></option>
                            </select>
                        </div>
                        <m3-buttons>
                            <m3-button type="success" icon="check" @button:clicked="move" flat></m3-button>
                            <m3-button type="danger" icon="times" @button:clicked="hideMovePopper" flat></m3-button>
                        </m3-buttons>
                    </div>
                </m3-popper>
            </div>
        </div>

        <m3-collapse :open="itemsOpen" v-if="hasItems()">
            <m3-explorer-item v-for="item in staticItems"
                :key="item.id"
                :collection="staticItems"
                :data="item"
                :groupId="groupId"
                :groupIsStatic="true"
                :settings="settings" />

            <m3-explorer-item v-for="item in dynamicItems"
                :key="item.id"
                :collection="dynamicItems"
                :collectionOptions="collectionOptions"
                :data="item"
                :groupId="groupId"
                :groupIsStatic="groupIsStatic"
                :settings="settings" />
        </m3-collapse>
    </div>
</template>

<script>
import axios from 'axios'
import M3Button from '@mach3builders/m3-components/M3Button'
import M3Buttons from '@mach3builders/m3-components/M3Buttons'
import M3Collapse from '@mach3builders/m3-components/M3Collapse'
import M3Icon from '@mach3builders/m3-components/M3Icon'
import M3Popper from '@mach3builders/m3-components/M3Popper'

export default {
    components: {
        M3Button,
        M3Buttons,
        M3Collapse,
        M3Icon,
        M3Popper,
    },

    props: {
        collection: Array,
        collectionOptions: Array,
        data: Object,
        groupId: Number,
        groupIsStatic: Boolean,
        settings: Object,
    },

    data() {
        return {
            focus: false,
            itemsOpen: false,
            loading: false,
            renaming: false,
            removeConfirmClass: false,
        }
    },

    computed: {
        itemClasses() {
            let classes = this.loading ? ' m3-loading' : ''
            classes += this.focus ? ' m3-focus' : ''
            classes += this.data.active ? ' m3-active' : ''
            return classes.trim()
        },

        expandIcon() {
            if (this.hasItems()) {
                return this.itemsOpen ? 'angle-down' : 'angle-right'
            }
            return null
        },

        icon() {
            return this.data.icon || 'folder'
        },

        actionsAllowed() {
            return (!this.groupIsStatic && !this.data.static && !this.renaming)
        },

        staticItems() {
            if (this.data.items) {
                return this.data.items.static || []
            }
            return []
        },

        dynamicItems() {
            if (this.data.items) {
                return this.data.items.length ? this.data.items : (this.data.items.dynamic || [])
            }
            return []
        }
    },

    beforeCreate () {
        this.$options.components.M3ExplorerItem = require('./M3ExplorerItem')
    },

    created() {
        this.$root.eventHub.$on('document:clicked', this.renamed)
    },

    beforeDestroy() {
        this.$root.eventHub.$off('document.clicked', this.renamed)
    },

    methods: {
        hasItems() {
            return this.staticItems.length || this.dynamicItems.length
        },

        toggleItems() {
            this.itemsOpen = !this.itemsOpen
        },

        blurItem() {
            this.focus = false
        },

        focusItem() {
            this.focus = true
        },

        click() {
            if (this.data.active) return

            if (this.settings.urls.getItem) {
                this.loading = true

                axios.get(this.settings.urls.getItem, {
                    params: {
                        group_id: this.groupId || 0,
                        id: this.data.id || 0,
                    }
                })
                .then((response) => {
                    this.loading = false
                    this.$set(this.data, 'active', true)
                    this.settings.events.getItem(response.data)

                    this.$root.eventHub.$emit('set-active-item', this.data)
                })
                .catch((error) => {
                    console.error(error)
                })
            }
        },

        add() {
            const ref = this.$refs['add-input']

            if (this.settings.urls.addItem && ref && ref.value.trim()) {
                const formData = new FormData()
                formData.append('group', this.groupId || 0)
                formData.append('parent', this.data.id || 0)
                formData.append('value', ref.value.trim() || '')

                // make request
                axios.post(this.settings.urls.addItem, formData)
                .then((response) => {
                    if (!this.data.items) {
                        this.$set(this.data, 'items', [])
                    }
                    this.data.items.push(response.data.data)

                    // open when closed
                    if (!this.itemsOpen) {
                        this.$nextTick(() => {
                            this.itemsOpen = true
                        })
                    }
                })
                .catch((error) => {
                    console.error(error)
                })

                this.hideAddPopper()
            }
        },

        move() {
            
        },

        rename() {
            this.renaming = true
            this.$root.eventHub.$emit('set-renaming-item', this)

            // focus on the input field, but first wait till the dom is updated
            this.$nextTick(() => {
                const ref = this.$refs['input']

                if (ref) {
                    ref.focus()
                    ref.setSelectionRange(0, ref.value.length)
                }
            })

            this.hideActionsPopper()
        },

        renamed() {
            if (this.renaming && this.data.name.trim()) {
                if (this.settings.urls.renameItem) {
                    this.loading = true

                    // create form data, so we can catch $_POST with PHP for instance...
                    const formData = new FormData()
                    formData.append('id', this.data.id || 0)
                    formData.append('group_id', this.groupId || 0)
                    formData.append('value', this.data.name.trim() || '')

                    axios.post(this.settings.urls.renameItem, formData)
                    .then((response) => {
                        this.loading = false
                        this.renaming = false
                        this.$root.eventHub.$emit('sort-items', this.collection)
                    })
                    .catch((error) => {
                        console.error(error)
                    })
                } else {
                    this.renaming = false
                }
            }
        },

        removeConfirm() {
            this.removeConfirmClass = true
        },

        removeCancelled(vm, event) {
            event.stopPropagation()
            this.removeConfirmClass = false
        },

        remove() {
            if (this.settings.urls.removeItem) {
                this.loading = true

                // create form data, so we can catch $_POST with PHP for instance...
                const formData = new FormData()
                formData.append('id', this.data.id || 0)
                formData.append('group_id', this.groupId || 0)

                axios.post(this.settings.urls.removeItem, formData)
                .then((response) => {
                    this.collection.splice(this.collection.indexOf(this.data), 1)
                    this.loading = false
                })
                .catch((error) => {
                    console.error(error)
                })
            }

            this.hideActionsPopper()
        },

        showActionsPopper(vm, event) {
            event.stopPropagation()
            this.removeCancelled(vm, event)

            const ref = this.$refs['actions-popper']
            ref.setDispatcher(vm)
            ref.show()

            this.$root.eventHub.$emit('hide-renaming-item-popper')
        },

        hideActionsPopper() {
            this.$refs['actions-popper'].hide()
        },

        showAddPopper() {
            this.focus = true
            const dispatcherRef = this.$refs['actions-button']
            const popperRef = this.$refs['add-popper']
            popperRef.setDispatcher(dispatcherRef)
            popperRef.show()

            // focus on the input field, but first wait till the dom is updated
            this.$nextTick(() => {
                const inputRef = this.$refs['add-input']
                if (inputRef) {
                    inputRef.focus()
                }
            })
        },

        hideAddPopper() {
            const popperRef = this.$refs['add-popper']
            const inputRef = this.$refs['add-input']

            if (popperRef && inputRef) {
                popperRef.hide()
                inputRef.value = ''
            }
        },

        showMovePopper() {
            this.focus = true
            const dispatcherRef = this.$refs['actions-button']
            const popperRef = this.$refs['move-popper']
            popperRef.setDispatcher(dispatcherRef)
            popperRef.show()

            // focus on the input field, but first wait till the dom is updated
            this.$nextTick(() => {
                const inputRef = this.$refs['move-input']
                if (inputRef) {
                    inputRef.focus()
                }
            })
        },

        hideMovePopper() {
            const popperRef = this.$refs['move-popper']
            const inputRef = this.$refs['move-input']

            if (popperRef && inputRef) {
                popperRef.hide()
                //inputRef.value = ''
            }
        },

        isOptionSelected(item) {
            if (this.data.items && this.data.items.includes(item)) {
                console.log(this.data.items)
                return true
            }
            return false
        }
    }
}
</script>

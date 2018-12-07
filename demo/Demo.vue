<template>
    <div id="app">
        <m3-explorer :settings="settings" />
    </div>
</template>

<script>
import M3Explorer from '../M3Explorer'

export default {
    components: {
        M3Explorer,
    },

    data() {
        return {
            settings: {
                text: {
                    addItemAction: 'Add folder',
                    moveItemAction: 'Move to',
                    moveItemSelectOption: 'Select a folder',
                    renameItemAction: 'Rename',
                    removeItemAction: 'Delete',
                },
                urls: {
                    loadData: 'data/load-items.json',
                    getItem: 'data/get-item.php',
                    addItem: 'data/add-item.php',
                    moveItem: 'data/move-item.php',
                    removeItem: 'data/remove-item.php',
                    renameItem: 'data/rename-item.php'
                }
            }
        }
    }
}
</script>

<style>
body {
    background-color: #eff3f6;
    margin: 0;
}

#app {
    padding: 1rem;
    width: 18rem;
}
</style>

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
                urls: {
                    loadData: 'data/load-data.json',
                    loadItemData: 'data/load-item-data.php',
                    addItem: 'data/add-item.php',
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

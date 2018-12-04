import Demo from './Demo.vue'

document.addEventListener('DOMContentLoaded', () => {
    new Vue({
    render: h => h(Demo),
    data() {
        return {
            eventHub: new Vue()
        }
    }
    }).$mount('#app')
})

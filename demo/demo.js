import Demo from './Demo.vue'

document.addEventListener('DOMContentLoaded', () => {
    new Vue({
    render: h => h(Demo),
    }).$mount('#app')
})

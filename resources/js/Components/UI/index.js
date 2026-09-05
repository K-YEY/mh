import UiButton from './UiButton.vue'
import UiInput from './UiInput.vue'
import UiCard from './UiCard.vue'
import UiSelect from './UiSelect.vue'

export default {
  install(app) {
    app.component('UiButton', UiButton)
    app.component('UiInput', UiInput)
    app.component('UiCard', UiCard)
    app.component('UiSelect', UiSelect)
  },
}

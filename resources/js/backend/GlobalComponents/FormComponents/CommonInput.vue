<template>
  <div :class="row_col_class" v-if="is_visible">
    <div class="form-group">
      <label for="">
        {{ label || name }}
      </label>
      <div
        v-if="
          [
            'text',
            'number',
            'password',
            'email',
          ].includes(type)
        "
        class="mt-1 mb-3"
      >
        <input
          class="form-control form-control-square mb-2"
          :type="type"
          :name="name"
          :id="name"
          :readonly="readonly"
          :value="value"
          v-bind:min="min"
          v-bind:step="step"
          @change="errorReset"
        />
      </div>

      <!-- DateTime inputs with clickable wrapper -->
      <div
        v-if="['date', 'time', 'datetime-local'].includes(type)"
        class="mt-1 mb-3"
      >
        <div 
          class="datetime-wrapper position-relative"
          @click="openDateTimePicker"
          style="cursor: pointer;"
        >
          <input
            ref="datetimeInput"
            class="form-control form-control-square mb-2"
            :type="type"
            :name="name"
            :id="name"
            :value="value"
            v-bind:min="min"
            @change="errorReset"
            style="pointer-events: none; cursor: pointer;"
          />
          <!-- Overlay to make entire area clickable -->
          <div 
            class="datetime-overlay position-absolute w-100 h-100"
            style="top: 0; left: 0; z-index: 1;"
          ></div>
        </div>
      </div>

      <div v-if="type === 'textarea'" class="mt-1 mb-3">
        <text-editor :name="name" />
      </div>

      <div v-if="type === 'select'" class="mt-1 mb-3">
        <select
          :name="name"
          class="form-control form-control-square"
          :id="name"
          @change="errorReset"
        >
          <option value="">Select item</option>
          <option
            v-for="data in data_list"
            :key="data"
            :value="data.value"
            :selected="data.value == value"
          >
            {{ data.label }}
          </option>
        </select>
      </div>
      <div v-if="type === 'file'" class="mt-1 mb-3">
        <image-component
          :name="name"
          :accept="`.jpg,.jpeg,.png`"
          :value="value"
        ></image-component>
      </div>
    </div>
  </div>
</template>

<script>
import TextEditor from "./TextEditor.vue";
import ImageComponent from "./ImageComponent.vue";
import { mapActions, mapState } from "pinia";
import { readonly } from "vue";
/**
 * props:
 */
export default {
  components: { TextEditor, ImageComponent },
  data: () => ({
    tag_input_value: "",
  }),
  props: {
    is_visible: {
      type: [Boolean, String],
      default: true,
    },
    name: {
      required: true,
      type: String,
    },
    label: {
      required: true,
      type: String,
    },
    type: {
      required: true,
      type: [String, Array, Object],
    },
    multiple: {
      required: false,
      type: [Boolean, String],
    },
    value: {
      required: false,
      type: [String, Number],
    },
    data_list: {
      required: false,
      type: Array,
    },
    min: {
      required: false,
      type: [String, Number],
    },
    step: {
      required: false,
      type: [String, Number],
    },
    readonly: {
      required: false,
      type: [Boolean, String],
    },
    images_list: {
      required: false,
      type: Array,
    },
    row_col_class: {
      required: false,
      type: String,
      default: "col-md-6",
    },
    onchange: {
      required: false,
      type: Function,
      default: () => "",
    },
    onchangeAction: {
      required: false,
      type: String,
      default: null,
    },
  },

  methods: {
    openDateTimePicker() {
      if (this.$refs.datetimeInput) {
        // Temporarily enable pointer events to allow focus
        this.$refs.datetimeInput.style.pointerEvents = 'auto';
        this.$refs.datetimeInput.focus();
        
        // For some browsers, we need to trigger the click event
        try {
          this.$refs.datetimeInput.showPicker();
        } catch (e) {
          // Fallback for browsers that don't support showPicker
          this.$refs.datetimeInput.click();
        }
        
        // Disable pointer events again after a short delay
        setTimeout(() => {
          if (this.$refs.datetimeInput) {
            this.$refs.datetimeInput.style.pointerEvents = 'none';
          }
        }, 100);
      }
    },

    errorReset(event) {
      let currentElement = event.target;
      let nextElement = currentElement.nextElementSibling;
      if (nextElement) {
        currentElement.classList.remove("border-warning");
        nextElement.remove();
      }
      if (this.onchange) {
        if (this.onchangeAction) {
          this.onchange(this.onchangeAction, event, this);
        } else {
          this.onchange(event);
        }
      }
    },

    removeTag: function (item) {
      this.remove_tag(item);
    },
  },
  created: async function () {},
};
</script>

<style scoped>
.datetime-wrapper {
  position: relative;
}

.datetime-wrapper:hover {
  opacity: 0.9;
}

.datetime-wrapper input {
  cursor: pointer !important;
}

.datetime-overlay {
  cursor: pointer;
}

/* Add visual feedback on hover */
.datetime-wrapper:hover input {
  border-color: #007bff;
  box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}
</style>
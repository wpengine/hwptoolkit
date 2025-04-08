<template>
  <div class="field-wrapper">
    <!-- Only render the label if it exists -->
    <label
      v-if="field.label"
      :for="field.databaseId"
      class="block text-sm font-medium text-gray-700"
    >
      {{ field.label }}
      <span v-if="field.isRequired" class="text-red-500">*</span>
    </label>
    <!-- Render a dynamic input based on the computed input type -->
    <input
      :id="field.databaseId"
      :type="computedInputType"
      v-model="internalValue"
      :placeholder="field.placeholder"
      :required="field.isRequired"
      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
    />
  </div>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
  field: {
    type: Object,
    required: true,
  },
  modelValue: {
    type: String,
    default: "",
  },
});

const emit = defineEmits(["update:modelValue"]);

// Create a computed property for v-model binding
const internalValue = computed({
  get() {
    return props.modelValue;
  },
  set(val) {
    emit("update:modelValue", val);
  },
});

// Determine the proper input type based on the field type
const computedInputType = computed(() => {
  const fieldType = props.field.type.toUpperCase();
  if (fieldType === "EMAIL") {
    return "email";
  } else if (fieldType === "WEBSITE") {
    return "url";
  }
  return "text";
});
</script>

<style scoped>
.field-wrapper {
  margin-bottom: 1rem;
}
</style>

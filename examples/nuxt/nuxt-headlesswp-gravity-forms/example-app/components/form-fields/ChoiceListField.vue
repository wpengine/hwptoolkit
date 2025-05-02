<template>
  <div class="field-wrapper">
    <label class="block text-sm font-medium text-gray-700 mb-2">
      {{ field.label }}
      <span v-if="field.isRequired" class="text-red-500">*</span>
    </label>
    <div class="space-y-2">
      <div
        v-for="(choice, index) in field.choices"
        :key="choice.value"
        class="flex items-center"
      >
        <input
          :id="`${field.databaseId}-${index}`"
          :type="inputType"
          :value="choice.value"
          :checked="isChecked(choice.value)"
          @change="handleChange(choice.value, $event.target.checked)"
          class="h-4 w-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500"
        />
        <label
          :for="`${field.databaseId}-${index}`"
          class="ml-2 block text-sm text-gray-700"
        >
          {{ choice.text }}
        </label>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
  field: { type: Object, required: true },
  modelValue: { type: Array, default: () => [] },
});

const emit = defineEmits(["update:modelValue"]);

// Determine the input type: use radio if field.type is "RADIO", otherwise checkbox.
const inputType = computed(() => {
  return props.field.type.toUpperCase() === "RADIO" ? "radio" : "checkbox";
});

const isChecked = (value) => {
  return Array.isArray(props.modelValue)
    ? props.modelValue.includes(value)
    : false;
};

const handleChange = (choiceValue, checked) => {
  let newValue = [];
  if (inputType.value === "checkbox") {
    // For checkboxes, allow multiple selections.
    const current = Array.isArray(props.modelValue) ? props.modelValue : [];
    newValue = checked
      ? [...current, choiceValue]
      : current.filter((v) => v !== choiceValue);
  } else {
    // For radio, single selection (store in array for consistency).
    newValue = [choiceValue];
  }
  emit("update:modelValue", newValue);
};
</script>

<style scoped>
.field-wrapper {
  margin-bottom: 1rem;
}
</style>

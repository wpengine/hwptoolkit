<template>
  <div class="field-wrapper">
    <label
      v-if="field.label"
      :for="field.databaseId"
      class="block text-sm font-medium text-gray-700"
    >
      {{ field.label }}
      <span v-if="field.isRequired" class="text-red-500">*</span>
    </label>
    <select
      :id="field.databaseId"
      v-model="internalValue"
      :required="field.isRequired"
      :multiple="isMultiple"
      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
    >
      <option disabled value="">{{ placeholder }}</option>
      <option
        v-for="choice in field.choices"
        :key="choice.value"
        :value="choice.value"
      >
        {{ choice.text }}
      </option>
    </select>
  </div>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
  field: { type: Object, required: true },
  modelValue: { type: [String, Array], default: "" },
});

const emit = defineEmits(["update:modelValue"]);

const internalValue = computed({
  get() {
    return props.modelValue;
  },
  set(val) {
    emit("update:modelValue", val);
  },
});

const isMultiple = computed(() => {
  return props.field.type.toUpperCase() === "MULTISELECT";
});

const placeholder = computed(() => {
  return props.field.placeholder || "Select an option";
});
</script>

<style scoped>
.field-wrapper {
  margin-bottom: 1rem;
}
</style>

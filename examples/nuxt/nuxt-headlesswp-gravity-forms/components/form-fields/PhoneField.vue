<script setup>
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
</script>

<template>
  <div class="field-wrapper">
    <label
      :for="field.databaseId"
      class="block text-sm font-medium text-gray-700"
    >
      {{ field.label }}
      <span v-if="field.isRequired" class="text-red-500">*</span>
    </label>
    <input
      :id="field.databaseId"
      type="tel"
      :placeholder="field.placeholder || '123-456-7890'"
      :required="field.isRequired"
      :value="modelValue"
      @input="emit('update:modelValue', $event.target.value)"
      pattern="[0-9]{3}-[0-9]{3}-[0-9]{4}"
      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
    />
    <small class="text-gray-500 mt-1 block">Format: 123-456-7890</small>
  </div>
</template>

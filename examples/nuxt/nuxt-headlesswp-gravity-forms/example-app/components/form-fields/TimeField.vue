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

const handleInput = (e) => {
  emit("update:modelValue", e.target.value);
};
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
      type="time"
      :required="field.isRequired"
      :value="modelValue"
      @input="handleInput"
      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
    />
  </div>
</template>

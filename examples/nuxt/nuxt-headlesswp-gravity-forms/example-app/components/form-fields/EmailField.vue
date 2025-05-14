<script setup>
import { ref } from "vue";

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
const errorMessage = ref("");

const handleInput = (e) => {
  const value = e.target.value;
  emit("update:modelValue", value);
  validateEmail(value);
};

const validateEmail = (email) => {
  const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/; // Regex for email validation
  if (!emailPattern.test(email)) {
    errorMessage.value = "Please enter a valid email address.";
  } else {
    errorMessage.value = ""; // Clear error message if valid
  }
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
      type="email"
      :placeholder="field.placeholder"
      :required="field.isRequired"
      :value="modelValue"
      @input="handleInput"
      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
    />
    <p v-if="errorMessage" class="text-red-500 text-sm">{{ errorMessage }}</p>
  </div>
</template>

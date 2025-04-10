<script setup>
import { reactive, watch } from "vue";

const props = defineProps({
  field: {
    type: Object,
    required: true,
  },
  modelValue: {
    type: Object,
    default: () => ({
      prefix: "",
      first: "",
      last: "",
      suffix: "",
    }),
  },
});

const emit = defineEmits(["update:modelValue"]);

const nameValues = reactive({ ...props.modelValue });

watch(
  nameValues,
  (newValues) => {
    emit("update:modelValue", { ...newValues });
  },
  { deep: true }
);
</script>

<template>
  <div class="field-wrapper">
    <label>{{ field.label }}</label>
    <div class="name-field-group">
      <input
        v-model="nameValues.prefix"
        placeholder="Prefix"
        class="prefix-field"
      />
      <input
        v-model="nameValues.first"
        placeholder="First Name"
        :required="field.isRequired"
        class="first-field"
      />
      <input
        v-model="nameValues.last"
        placeholder="Last Name"
        :required="field.isRequired"
        class="last-field"
      />
      <input
        v-model="nameValues.suffix"
        placeholder="Suffix"
        class="suffix-field"
      />
    </div>
  </div>
</template>

<style scoped>
.name-field-group {
  display: grid;
  grid-template-columns: 0.7fr 2fr 2fr 0.7fr;
  gap: 10px;
}

.name-field-group input {
  margin-bottom: 0;
}
</style>

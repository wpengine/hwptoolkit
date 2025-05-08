<script setup>
import { ref, computed } from "vue";

const props = defineProps({
  field: {
    type: Object,
    required: true,
  },
  modelValue: {
    type: Object,
    default: () => ({
      street: "",
      lineTwo: "",
      city: "",
      state: "",
      zip: "",
      country: "US",
    }),
  },
});

const emit = defineEmits(["update:model-value"]);
const errorMessage = ref("");

const postalCodePatterns = {
  US: /^\d{5}(-\d{4})?$/,
  CA: /^[A-Za-z]\d[A-Za-z] \d[A-Za-z]\d$/,
  GB: /^([Gg][Ii][Rr] 0[Aa]{2}|[A-Za-z]{1,2}[0-9][0-9]?[A-Za-z]?[ ]?[0-9][A-Za-z]{2})$/,
  AU: /^\d{4}$/,
  NZ: /^\d{4}$/,
};

const updateField = (field, value) => {
  const newValue = {
    ...props.modelValue,
    [field]: value,
  };
  emit("update:model-value", newValue);
  validateAddress(newValue);
};

const validateAddress = (address) => {
  const pattern = postalCodePatterns[address.country];

  if (!address.street || !address.city || !address.state || !address.zip) {
    errorMessage.value = "Please fill out all required fields.";
  } else if (pattern && !pattern.test(address.zip)) {
    errorMessage.value =
      "Please enter a valid postal code for the selected country.";
  } else {
    errorMessage.value = "";
  }
};
</script>

<template>
  <div class="field-wrapper">
    <label>{{ field.label }}</label>
    <div class="address-field-group">
      <div class="full-width">
        <input
          :value="modelValue.street"
          @input="(e) => updateField('street', e.target.value)"
          placeholder="Street Address"
          :required="field.isRequired"
          class="form-input"
        />
      </div>
      <div class="full-width">
        <input
          :value="modelValue.lineTwo"
          @input="(e) => updateField('lineTwo', e.target.value)"
          placeholder="Address Line 2"
          class="form-input"
        />
      </div>
      <div class="city-state-group">
        <input
          :value="modelValue.city"
          @input="(e) => updateField('city', e.target.value)"
          placeholder="City"
          :required="field.isRequired"
          class="form-input"
        />
        <input
          :value="modelValue.state"
          @input="(e) => updateField('state', e.target.value)"
          placeholder="State/Province"
          :required="field.isRequired"
          class="form-input"
        />
      </div>
      <div class="zip-country-group">
        <input
          :value="modelValue.zip"
          @input="(e) => updateField('zip', e.target.value)"
          placeholder="ZIP/Postal Code"
          :required="field.isRequired"
          class="form-input"
        />
        <select
          :value="modelValue.country"
          @change="(e) => updateField('country', e.target.value)"
          :required="field.isRequired"
          class="form-input"
        >
          <option value="US">United States</option>
          <option value="CA">Canada</option>
          <option value="GB">United Kingdom</option>
          <option value="AU">Australia</option>
          <option value="NZ">New Zealand</option>
        </select>
      </div>
    </div>
    <p v-if="errorMessage" class="text-red-500 text-sm">
      {{ errorMessage }}
    </p>
  </div>
</template>

<style scoped>
.address-field-group {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.full-width {
  width: 100%;
}

.city-state-group,
.zip-country-group {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.form-input {
  width: 100%;
  padding: 0.5rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  margin: 0;
}

.form-input:hover {
  border-color: #b3b3b3;
}

.form-input:focus {
  outline: none;
  border-color: #007bff;
  box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
}
</style>

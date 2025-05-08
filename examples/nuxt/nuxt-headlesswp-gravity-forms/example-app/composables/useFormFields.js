import { defineAsyncComponent } from "vue";

export const useFormFields = () => {
  const loggedTypes = new Set();

  /**
   * Resolves the Vue component for a given field based on its inputType.
   * If inputType is not present, falls back to using type.
   *
   * @param {Object} field - The Gravity Form field object.
   * @returns {Component|null} The async Vue component for this field.
   */
  const resolveFieldComponent = (field) => {
    const fieldType = field.inputType
      ? field.inputType.toUpperCase()
      : field.type.toUpperCase();

    const typeToComponent = {
      ADDRESS: "AddressField",
      TEXT: "InputField",
      TEXTAREA: "InputField",
      EMAIL: "EmailField",
      NAME: "NameField",
      PHONE: "PhoneField",
      SELECT: "DropdownField",
      MULTISELECT: "DropdownField",
      CHECKBOX: "ChoiceListField",
      RADIO: "ChoiceListField",
      DATE: "DateField",
      TIME: "TimeField",
      WEBSITE: "InputField",
      // Add any additional mappings if needed.
    };

    // Log the field type for debugging on the first occurrence.
    if (!loggedTypes.has(fieldType)) {
      console.log("Mapping field type:", fieldType);
      loggedTypes.add(fieldType);
    }

    const componentName = typeToComponent[fieldType];
    return componentName
      ? defineAsyncComponent(() =>
          import(`~/components/form-fields/${componentName}.vue`)
        )
      : null;
  };

  return {
    resolveFieldComponent,
  };
};

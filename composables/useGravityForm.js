import { ref } from "vue";
import { useRuntimeConfig } from "#app";

export default function useGravityForm() {
  const config = useRuntimeConfig();
  const formFields = ref([]);

  const formQuery = `
  query GetGravityForm($formId: ID!) {
  gfForm(id: $formId, idType: DATABASE_ID) {
    formFields(first: 300) {
      nodes {
        id
        databaseId
        inputType
        type
        visibility
        ... on GfFieldWithLabelSetting {
          label
        }
        ... on GfFieldWithRulesSetting {
          isRequired
        }
        ... on GfFieldWithCssClassSetting {
          cssClass
        }
        ... on GfFieldWithDefaultValueSetting {
          defaultValue
        }
        ... on GfFieldWithSizeSetting {
          size
        }
        ... on GfFieldWithPlaceholderSetting {
          placeholder
        }
        ... on GfFieldWithMaxLengthSetting {
          maxLength
        }
        ... on GfFieldWithInputMaskSetting {
          inputMaskValue
        }
        ... on GfFieldWithChoicesSetting {
          choices {
            text
            value
          }
          inputs {
            id
            label
          }
        }
        ... on GfFieldWithConditionalLogicSetting {
          conditionalLogic {
            actionType
            logicType
            rules {
              fieldId
              operator
              value
            }
          }
        }
      }
    }
  }
}


  `;

  const fetchForm = () => {
    console.log("Making GraphQL request:", {
      url: config.public.wordpressUrl,
      query: formQuery,
    });

    const { data, status, fetchError, execute, refresh } = useFetch(
      config.public.wordpressUrl,
      {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
        },
        body: JSON.stringify({
          query: formQuery,
          variables: { formId: "1" },
        }),
        immediate: false,
        transform: (res) => {
          if (res.errors) {
            console.error("GraphQL Errors:", res.errors);
            throw new Error(res.errors[0].message);
          }
          const fields = res.data?.gfForm?.formFields?.nodes;
          if (!Array.isArray(fields)) {
            console.error("Invalid fields data:", res.data);
            throw new Error("Invalid form fields data");
          }
          return fields;
        },
      }
    );

    return { data, status, fetchError, execute, refresh };
  };

  const transformFieldValue = (field, value) => {
    if (!field) return null;
    const fieldId = parseInt(field.databaseId, 10);
    switch (field.type) {
      case "CHECKBOX":
        if (!Array.isArray(value) || !value.length) return null;
        return {
          id: fieldId,
          checkboxValues: value.map((val, index) => ({
            inputId: parseFloat(`${fieldId}.${index + 1}`),
            value: val,
          })),
        };
      case "ADDRESS":
        return {
          id: fieldId,
          addressValues: {
            street: value?.street || "",
            lineTwo: value?.lineTwo || "",
            city: value?.city || "",
            state: value?.state || "",
            zip: value?.zip || "",
            country: value?.country || "US",
          },
        };
      case "EMAIL":
        return {
          id: fieldId,
          emailValues: {
            value: value || "",
            confirmationValue: value || "",
          },
        };
      case "NAME":
        return {
          id: fieldId,
          nameValues: {
            prefix: value?.prefix || "",
            first: value?.first || "",
            middle: value?.middle || "",
            last: value?.last || "",
            suffix: value?.suffix || "",
          },
        };
      case "MULTISELECT":
      case "POST_CATEGORY":
      case "POST_CUSTOM":
      case "POST_TAGS":
        return {
          id: fieldId,
          values: Array.isArray(value) ? value : [],
        };
      default:
        return {
          id: fieldId,
          value: value?.toString() || "",
        };
    }
  };

  const submitForm = async (formId, fieldValues) => {
    try {
      const transformedValues = Object.entries(fieldValues)
        .map(([id, value]) => {
          const field = formFields.value.find(
            (f) => f.databaseId === parseInt(id, 10)
          );
          if (!field) {
            console.warn(`No field found for ID ${id}`);
            return null;
          }
          return transformFieldValue(field, value);
        })
        .filter(Boolean);

      const mutation = `
        mutation SubmitForm($formId: ID!, $fieldValues: [FormFieldValuesInput!]!) {
          submitGfForm(input: {
            id: $formId
            fieldValues: $fieldValues
          }) {
            errors {
              id
              message
            }
            confirmation {
              message
              type
            }
            entry {
              id
              ... on GfSubmittedEntry {
                databaseId
              }
            }
          }
        }
      `;

      const response = await fetch(config.public.wordpressUrl, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
        },
        body: JSON.stringify({
          query: mutation,
          variables: {
            formId: parseInt(formId, 10),
            fieldValues: transformedValues,
          },
        }),
      });

      const result = await response.json();

      if (result.errors) {
        throw new Error(result.errors.map((e) => e.message).join(", "));
      }

      return result.data.submitGfForm;
    } catch (error) {
      console.error("Submit form error:", error);
      throw error;
    }
  };

  return { formFields, fetchForm, submitForm };
}

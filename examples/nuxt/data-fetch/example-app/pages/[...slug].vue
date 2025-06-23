<script setup>
import { uriToTemplate } from '../lib/templateHierarchy'
import { computed } from 'vue'

const uri = useRoute().path || '/'

const { data: templateData, error } = await useAsyncData(`template-${uri}`, () =>
  uriToTemplate({ uri })
)

// Dynamically load the component from `components/templates/<template.id>.vue`
const component = computed(() => {
  const templateId = templateData.value?.template?.id
  if (!templateId) return null
  return defineAsyncComponent(() => import(`~/components/wp-templates/${templateId}.vue`))
})

</script>

<template>
<component
  :is="component"
  v-if="component"
  :template-data="templateData"
/>
  <div v-else-if="error">
    <p>Error loading template: {{ error.message }}</p>
  </div>
  <div v-else>
    <p>Loading...</p>
  </div>
</template>
<script setup>
import { uriToTemplate } from '../lib/templateHierarchy'
import { computed } from 'vue'
import NotFound from '../components/404.vue'
const uri = useRoute().path || '/'

const { data: templateData, error } = await useAsyncData(`template-${uri}`, () =>
  uriToTemplate({ uri })
)

const component = computed(() => {
  const templateId = templateData.value?.template?.id
  if (!templateId) return null
  return defineAsyncComponent(() => import(`~/components/wp-templates/${templateId}.vue`))
})

</script>

<template>
  <component :is="component" v-if="component" :template-data="templateData" />
  <div v-else-if="error">
    <NotFound text="Error loading template" />
  </div>
</template>
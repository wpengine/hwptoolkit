<script setup>
import { computed } from 'vue';
import { useGraphQL, gql } from '../lib/client';

const FOOTER_QUERY = gql`
  query GetFooterData {
    generalSettings {
      title
    }        
  }
`;

const { 
  data: settingsData, 
  loading: settingsLoading,
} = useGraphQL(FOOTER_QUERY, {}, { 
  key: 'footer-settings',
  loadingText: 'Loading site title...' 
});

const siteInfo = computed(() => {
  const title = settingsData.value?.generalSettings?.title;  
  return {
    title: title
  };
});
const currentYear = new Date().getFullYear();
</script>

 <template>
  <footer v-if="!settingsLoading" class="footer">
      <div class="footer-content">
        <p>© {{ currentYear }} <NuxtLink to="/">{{ siteInfo.title }}</NuxtLink>. All rights reserved.</p>
        <p>Built with WordPress, Nuxt, and WPGraphQL.</p>
      </div>  
  </footer>
</template>

<style scoped lang="scss">
@use '@/assets/scss/components/footer';
</style>
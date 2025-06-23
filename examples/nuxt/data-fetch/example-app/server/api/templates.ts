import { readdir } from 'node:fs/promises'
import { join } from 'node:path'

export default defineEventHandler(async () => {
  const TEMPLATE_PATH = 'components/wp-templates'

  const files = await readdir(join(process.cwd(), TEMPLATE_PATH))

  const templates = files
    .filter((file) => file.endsWith('.vue') && !file.startsWith('+') && !file.startsWith('_'))
    .map((file) => {
      const slug = file.replace('.vue', '')
      return {
        id: slug,
        path: `/${TEMPLATE_PATH}/${slug}`,
      }
    })

  return templates
})

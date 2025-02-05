import { defineConfig } from '@playwright/test';

export default defineConfig({
  testDir: './tests',
  use: {
    screenshot: 'only-on-failure',
  },
  projects: [
    {
      name: 'chromium',
      use: { browserName: 'chromium' },
    },
    {
      name: 'firefox',
      use: { browserName: 'firefox' },
    },
    {
      name: 'webkit',
      use: { browserName: 'webkit' },
    },
    {
      name: 'node',
      testMatch: /cli\/.*\.test\.js/,
    }
  ],
});

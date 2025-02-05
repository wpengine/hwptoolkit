import { test, expect } from '@playwright/test';
import { execSync } from 'child_process';
import stripAnsi from 'strip-ansi';

test.describe('hwp plugins command', () => {
  test('should list installed HWP plugins', async () => {
    const output = execSync('pnpm hwp plugins', {
      cwd: process.cwd(),
      env: {
        ...process.env,
        WP_URL: 'http://localhost:8889'
      }
    }).toString();

    const result = stripAnsi(output);

    // Check plugins list format
    expect(result).toContain('Installed HWP Plugins:');
    
    // Should at least contain the CLI plugin
    expect(result).toMatch(/HWP CLI Plugin v\d+\.\d+\.\d+/);
    expect(result).toMatch(/Status:\s+(Active|Inactive)/);
    expect(result).toMatch(/NPM Package:/);
  });

  test('should handle empty plugin list', async () => {
    const output = execSync('pnpm hwp plugins', {
      cwd: process.cwd(),
      env: {
        ...process.env,
        WP_URL: 'http://localhost:8889'
      }
    }).toString();

    const result = stripAnsi(output);

    // Should either show plugins or indicate none are installed
    expect(result).toMatch(/(Installed HWP Plugins:|No HWP plugins installed)/);
  });

  test('should handle connection errors gracefully', async () => {
    try {
      execSync('pnpm hwp plugins', {
        cwd: process.cwd(),
        env: {
          ...process.env,
          WP_URL: 'http://nonexistent-site:8889'
        }
      });
    } catch (error) {
      const result = stripAnsi(error.toString());
      expect(result).toContain('Unable to fetch plugin list');
      expect(result).toContain('http://nonexistent-site:8889');
    }
  });
});

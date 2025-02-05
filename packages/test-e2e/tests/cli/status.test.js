import { test, expect } from '@playwright/test';
import { execSync } from 'child_process';
import stripAnsi from 'strip-ansi';

test.describe('hwp status command', () => {
  test('should return WordPress and plugin status', async () => {
    const output = execSync('pnpm hwp status', {
      cwd: process.cwd(),
      env: {
        ...process.env,
        WP_URL: 'http://localhost:8889'
      }
    }).toString();

    const result = stripAnsi(output);

    // Check WordPress Status section
    expect(result).toContain('WordPress Status:');
    expect(result).toMatch(/Environment:\s+\w+/);
    expect(result).toMatch(/URL:\s+http/);
    expect(result).toMatch(/Version:\s+\d+\.\d+\.\d+/);
    expect(result).toMatch(/Debug Mode:\s+(Enabled|Disabled)/);

    // Check HWP Status section
    expect(result).toContain('HWP Status:');
    expect(result).toContain('Plugin: hwp-cli');
    expect(result).toMatch(/Status:\s+(Active|Inactive)/);
    expect(result).toMatch(/Version:\s+\d+\.\d+\.\d+/);
    expect(result).toMatch(/REST API:\s+(Available|Unavailable)/);
  });

  test('should handle connection errors gracefully', async () => {
    try {
      execSync('pnpm hwp status', {
        cwd: process.cwd(),
        env: {
          ...process.env,
          WP_URL: 'http://nonexistent-site:8889'
        }
      });
    } catch (error) {
      const result = stripAnsi(error.toString());
      expect(result).toContain('Unable to connect to WordPress site');
      expect(result).toContain('http://nonexistent-site:8889');
    }
  });
});

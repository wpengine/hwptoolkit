import { Command } from 'commander';
import chalk from 'chalk';
import { config } from 'dotenv';
import { fileURLToPath } from 'url';
import { dirname, resolve } from 'path';

// Load environment variables from .env file
const __dirname = dirname(fileURLToPath(import.meta.url));
config({ path: resolve(process.cwd(), '.env') });

const program = new Command();
const WP_URL = process.env.WP_URL || 'http://localhost:8889';

program
  .name('hwp')
  .description('Headless WordPress Toolkit CLI')
  .version('1.0.0');

program
  .command('status')
  .description('Check WordPress site and plugin status')
  .action(async () => {
    try {
      const response = await fetch(`${WP_URL}/wp-json/hwp/v1/cli/status`);
      const data = await response.json();
      
      console.log('\nWordPress Status:\n');
      console.log(`Environment: ${chalk.cyan(data.environment)}`);
      console.log(`URL: ${chalk.cyan(data.url)}`);
      console.log(`Version: ${chalk.cyan(data.wp_version)}`);
      console.log(`Debug Mode: ${data.wp_debug ? chalk.yellow('Enabled') : chalk.green('Disabled')}`);
      
      console.log('\nHWP Status:\n');
      console.log(`Plugin: ${chalk.cyan('hwp-cli')}`);
      console.log(`Status: ${data.status === 'active' ? chalk.green('Active') : chalk.red('Inactive')}`);
      console.log(`Version: ${chalk.cyan(data.version)}`);
      console.log(`REST API: ${data.rest_api ? chalk.green('Available') : chalk.red('Unavailable')}`);
    } catch (error) {
      console.error(chalk.red('Error: Unable to connect to WordPress site'));
      console.error(chalk.yellow(`Attempted to connect to: ${WP_URL}`));
    }
  });

program
  .command('plugins')
  .description('List installed HWP plugins')
  .action(async () => {
    try {
      const response = await fetch(`${WP_URL}/wp-json/hwp/v1/cli/plugins`);
      const plugins = await response.json();
      
      if (plugins.length === 0) {
        console.log(chalk.yellow('\nNo HWP plugins installed.'));
        return;
      }

      console.log('\nInstalled HWP Plugins:\n');
      plugins.forEach(plugin => {
        console.log(chalk.cyan(`${plugin.Name} v${plugin.Version}`));
        console.log(`Status: ${plugin.active ? chalk.green('Active') : chalk.red('Inactive')}`);
        console.log(`NPM Package: ${chalk.gray(plugin['NPM Package'] || 'Not specified')}`);
        console.log('---');
      });
    } catch (error) {
      console.error(chalk.red('Error: Unable to fetch plugin list'));
      console.error(chalk.yellow(`Attempted to connect to: ${WP_URL}`));
    }
  });

export { program };

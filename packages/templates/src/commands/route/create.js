const { program } = require('commander');
const { select, Separator } = require('@inquirer/prompts');

const fs = require('fs');
const path = require('path');
const os = require('os');
const config = require('../../sample');

const ejs = require('ejs');


async function createRoute(routeMappings = []) {
  const framework = config.getFramework();

  // @TODO fill out with correct questions etc

  // @TODO this is user configuration generated in setup
  console.info(`Creating route using ${framework}...`);

  const routeType = await select({
    message: 'Select route type',
    choices: [
      {
        name: 'config',
        value: 'config',
        description: 'Generate as config for next.config.js',
      },
      {
        name: 'middleware',
        value: 'middleware',
        description: 'Generate as config within a middleware function',
      },
      new Separator(),
      {
        name: 'middleware-hybrid', // Example
        value: 'middleware-hybrid',
        disabled: true,
      }
    ],
  });

    console.log('Generating routing for ' + routeType);

    // TODO Refactor
    const templatePath = path.join(__dirname, 'templates/' + routeType + '.ejs');
    const tmpDir = path.join(__dirname, '../../../../../tmp');
    const outputPath = path.join(tmpDir, 'next.config.mjs');

    ejs.renderFile(templatePath, { routeMappings }, (err, result) => {
      if (err) {
        console.error('Error rendering template:', err);
        return;
      }

      fs.writeFileSync(outputPath, result, 'utf8');
      console.log(`Generated file: ${outputPath}`);
    });
}

module.exports = createRoute;

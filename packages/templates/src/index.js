const { program } = require('commander');
const { input, confirm } = require('@inquirer/prompts');
const createRoute = require('./commands/route/create');
// const createTemplate = require('./commands/generate/template');

async function createRouteMapping(routeMapping = []) {

  // @TODO add better validation
  const route = await input({
    message: 'Enter the WordPress (e.g., /blog/:slug*):',
    validate: (input) => input.startsWith('/') ? true : 'Route must start with a "/"',
    default: '/blog/:slug*'
  });

  const directory = await input({
    message: 'Enter the directory to map this route to (e.g., /news/:slug*):',
    validate: (input) => input.startsWith('/') ? true : 'Directory must start with a "/"',
    default: '/news/:slug*'
  });

  routeMapping.push({route, directory});


  const addAnotherRoute = await confirm({
    message: 'Add another route?',
    default: true
  });

  if (addAnotherRoute) {
    return createRouteMapping(routeMapping);
  }

  return routeMapping;
}

program
  .command('hwptoolkit:template:route:create-mapping')
  .description('Create new route configuration for WordPress Template Hierarchy')
  .action(async () => {
    const routeMapping = await createRouteMapping();
    console.log('Route mappings: ', routeMapping);
    createRoute(routeMapping);
  });

// program
//   .command('hwp:template:generate:file')
//   .description('Creates a template file for one of the WordPress Templates')
//   .action(createTemplate);

program.parse(process.argv);

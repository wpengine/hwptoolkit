import type { Handle } from "@sveltejs/kit";

export const handle = (async ({ event, resolve }) => {
  return resolve(event, {
    filterSerializedResponseHeaders: () => true, // basically get all headers
  });
}) satisfies Handle;

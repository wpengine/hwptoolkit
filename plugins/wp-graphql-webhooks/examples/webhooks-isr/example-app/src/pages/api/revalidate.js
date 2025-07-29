import crypto from 'crypto';

export default async function handler(req, res) {
  try {
    console.log('[Webhook] Received revalidation request');

    const secret = req.headers['x-webhook-secret'];
    const expectedSecret = process.env.WEBHOOK_REVALIDATE_SECRET;

    console.log('[Webhook] Secret from header:', secret ? 'Provided' : 'Missing');
    console.log('[Webhook] Expected secret is set:', expectedSecret ? 'Yes' : 'No');

    // Securely compare secrets
    if (
      !secret ||
      !expectedSecret ||
      secret.length !== expectedSecret.length ||
      !crypto.timingSafeEqual(Buffer.from(secret), Buffer.from(expectedSecret))
    ) {
      console.warn('[Webhook] Invalid secret token');
      return res.status(401).json({ message: 'Invalid token' });
    }
    console.log('[Webhook] Secret token validated successfully');

    if (req.method !== 'POST') {
      return res.status(405).json({ message: 'Method Not Allowed' });
    }

    const body = req.body;
    console.log('[Webhook] Request body parsed:', body);

    const path = body.path;

    if (!path || typeof path !== 'string') {
      console.warn('[Webhook] Invalid or missing path in request body');
      return res.status(400).json({ message: 'Path is required' });
    }
    console.log('[Webhook] Path to revalidate:', path);

    await res.revalidate(path);
    console.log('[Webhook] Successfully revalidated path:', path);

    return res.status(200).json({ message: `Revalidated path: ${path}` });
  } catch (error) {
    console.error('[Webhook] Revalidation error:', error);
    return res.status(500).json({ message: 'Error during revalidation' });
  }
}

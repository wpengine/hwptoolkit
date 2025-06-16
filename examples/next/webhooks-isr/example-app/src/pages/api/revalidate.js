import crypto from 'crypto';

export default async function handler(req, res) {
  if (req.method !== 'POST') {
    return res.status(405).json({ message: 'Method not allowed' });
  }

  try {
    // Log the full webhook payload
    console.log('\n========== WEBHOOK RECEIVED ==========');
    console.log('Timestamp:', new Date().toISOString());
    console.log('Headers:', JSON.stringify(req.headers, null, 2));
    console.log('Payload:', JSON.stringify(req.body, null, 2));
    console.log('=====================================\n');

    // Verify secret
    const secret = req.headers['x-webhook-secret'];
    const expectedSecret = process.env.WEBHOOK_REVALIDATE_SECRET;
    
    console.log('[Webhook] Secret header present:', !!secret);
    console.log('[Webhook] Expected secret present:', !!expectedSecret);
    
    if (!secret || !expectedSecret) {
      console.log('[Webhook] Missing secret configuration');
      return res.status(401).json({ message: 'Unauthorized' });
    }

    // Use timing-safe comparison
    const secretBuffer = Buffer.from(secret);
    const expectedBuffer = Buffer.from(expectedSecret);
    
    if (secretBuffer.length !== expectedBuffer.length || 
        !crypto.timingSafeEqual(secretBuffer, expectedBuffer)) {
      console.log('[Webhook] Invalid secret');
      return res.status(401).json({ message: 'Unauthorized' });
    }

    console.log('[Webhook] Secret validated successfully');

    // Extract path from various possible locations in the payload
    let path = req.body?.path || 
               req.body?.post?.path || 
               req.body?.post?.uri ||
               req.body?.uri ||
               req.query?.path;

    if (!path) {
      console.log('[Webhook] No path found in payload');
      return res.status(400).json({ message: 'Path is required' });
    }
    
    console.log('\n========== ISR REVALIDATION ==========');
    console.log('Path to revalidate:', path);
    console.log('Starting at:', new Date().toISOString());

    // Perform revalidation
    await res.revalidate(path);
    
    console.log('✅ SUCCESS: Revalidated path:', path);
    console.log('Completed at:', new Date().toISOString());
    console.log('=====================================\n');

    return res.status(200).json({ 
      message: `Revalidated path: ${path}`,
      revalidatedAt: new Date().toISOString(),
      info: 'Only this specific page was regenerated, not the entire site'
    });
  } catch (error) {
    console.error('\n========== REVALIDATION ERROR ==========');
    console.error('Error:', error);
    console.error('=======================================\n');
    return res.status(500).json({ message: 'Error during revalidation' });
  }
}

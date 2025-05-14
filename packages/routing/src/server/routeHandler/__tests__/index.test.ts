import { notFound } from 'next/navigation';
import { GetFn, PostFn, routeHandler, createRouteHandler } from '../index';
import { tokenHandler } from '../tokenHandler';

// Mock dependencies
jest.mock('../tokenHandler', () => ({
  tokenHandler: jest.fn().mockResolvedValue({ status: 200 }),
}));

describe('Route Handler', () => {
  // Reset mocks between tests
  beforeEach(() => {
    jest.clearAllMocks();
  });

  describe('GetFn', () => {
    it('should route /api/hwp/token to tokenHandler', async () => {
      const req = new Request('http://localhost:3000/api/hwp/token');
      await GetFn(req);
      expect(tokenHandler).toHaveBeenCalledWith(req);
    });

    it('should route /api/hwp/token/ (with trailing slash) to tokenHandler', async () => {
      const req = new Request('http://localhost:3000/api/hwp/token/');
      await GetFn(req);
      expect(tokenHandler).toHaveBeenCalledWith(req);
    });

    it('should return 404 for unknown routes', async () => {
      const req = new Request('http://localhost:3000/api/hwp/unknown');
      await GetFn(req);
      expect(notFound).toHaveBeenCalled();
    });
  });

  describe('PostFn', () => {
    it('should return 404 for any route by default', async () => {
      const req = new Request('http://localhost:3000/api/hwp/any-route', {
        method: 'POST',
      });
      await PostFn(req);
      expect(notFound).toHaveBeenCalled();
    });
  });

  describe('routeHandler', () => {
    it('should have GET and POST methods', () => {
      expect(typeof routeHandler.GET).toBe('function');
      expect(typeof routeHandler.POST).toBe('function');
    });

    it('GET should call GetFn', async () => {
      const req = new Request('http://localhost:3000/api/hwp/token');
      // Mock GetFn
      const originalGetFn = GetFn;
      const mockGetFn = jest.fn().mockResolvedValue({ status: 200 });
      
      // @ts-ignore - Replace the function temporarily for testing
      global.GetFn = mockGetFn;
      
      await routeHandler.GET(req);
      
      // Not ideal to test implementation details, but ensures proper delegation
      expect(tokenHandler).toHaveBeenCalledWith(req);
      
      // Restore original
      // @ts-ignore
      global.GetFn = originalGetFn;
    });
  });

  describe('createRouteHandler', () => {
    it('should return an object with GET and POST methods', () => {
      const handler = createRouteHandler();
      expect(typeof handler.GET).toBe('function');
      expect(typeof handler.POST).toBe('function');
    });
  });
});


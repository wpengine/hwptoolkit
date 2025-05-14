import { NextResponse } from 'next/server';
import { cookies } from 'next/headers';
import { tokenHandler } from '../tokenHandler';
import { getWpUrl, getWpSecret } from '../../../lib/config';

// Mock global fetch
const mockFetchResponse = {
  ok: true,
  json: jest.fn().mockResolvedValue({
    accessToken: 'test-access-token',
    accessTokenExpiration: 3600,
    refreshToken: 'test-refresh-token',
    refreshTokenExpiration: 86400,
  }),
};

global.fetch = jest.fn().mockResolvedValue(mockFetchResponse);

// Mock dependencies
jest.mock('../../../lib/config', () => ({
  getWpUrl: jest.fn().mockReturnValue('https://example.com'),
  getWpSecret: jest.fn().mockReturnValue('test-secret-key'),
}));

describe('Token Handler', () => {
  const cookieName = 'https://example.com-rt';
  const mockCookieStore = cookies();

  beforeEach(() => {
    jest.clearAllMocks();
    
    // Default mock responses
    (mockCookieStore.get as jest.Mock).mockReturnValue(undefined);
    (mockFetchResponse.json as jest.Mock).mockResolvedValue({
      accessToken: 'test-access-token',
      accessTokenExpiration: 3600,
      refreshToken: 'test-refresh-token',
      refreshTokenExpiration: 86400,
    });
    (global.fetch as jest.Mock).mockResolvedValue({
      ...mockFetchResponse,
      ok: true,
    });
  });

  describe('with authorization code', () => {
    it('should request tokens from WordPress with a code', async () => {
      const req = new Request('http://localhost:3000/api/hwp/token?code=test-code');
      
      await tokenHandler(req);
      
      expect(global.fetch).toHaveBeenCalledWith(
        expect.stringContaining('authorize'),
        expect.objectContaining({
          method: 'POST',
          headers: expect.objectContaining({
            'x-faustwp-secret': 'test-secret-key',
          }),
          body: expect.stringContaining('"code":"test-code"'),
        })
      );
    });

    it('should return tokens and set refresh token cookie on success', async () => {
      const req = new Request('http://localhost:3000/api/hwp/token?code=test-code');
      
      await tokenHandler(req);
      
      expect(NextResponse.json).toHaveBeenCalledWith(
        expect.objectContaining({
          accessToken: 'test-access-token',
          refreshToken: 'test-refresh-token',
        }),
        expect.anything()
      );
      
      // Check cookie was set
      const mockResponse = await NextResponse.json({});
      expect(mockResponse.cookies.set).toHaveBeenCalledWith(
        cookieName,
        'test-refresh-token',
        expect.objectContaining({
          expires: expect.any(Date),
          httpOnly: true,
        })
      );
    });
  });

  describe('with refresh token', () => {
    it('should use refresh token from cookie if no code is provided', async () => {
      const req = new Request('http://localhost:3000/api/hwp/token');
      
      // Mock refresh token in cookie
      (mockCookieStore.get as jest.Mock).mockReturnValue({
        name: cookieName,
        value: 'existing-refresh-token',
      });
      
      await tokenHandler(req);
      
      expect(global.fetch).toHaveBeenCalledWith(
        expect.stringContaining('authorize'),
        expect.objectContaining({
          body: expect.stringContaining('"refreshToken":"existing-refresh-token"'),
        })
      );
    });
  });

  describe('error handling', () => {
    it('should return 401 when no code or refresh token is provided', async () => {
      const req = new Request('http://localhost:3000/api/hwp/token');
      
      const response = await tokenHandler(req);
      
      expect(response.status).toBe(401);
      const responseBody = await response.json();
      expect(responseBody.error).toBe('Unauthorized');
    });

    it('should return 401 when WordPress returns an error', async () => {
      const req = new Request('http://localhost:3000/api/hwp/token?code=invalid-code');
      
      // Mock WordPress error response
      (global.fetch as jest.Mock).mockResolvedValue({
        ok: false,
        status: 401,
        json: jest.fn().mockResolvedValue({
          message: 'Invalid code',
        }),
      });
      
      const response = await tokenHandler(req);
      
      expect(response.status).toBe(401);
      const responseBody = await response.json();
      expect(responseBody.error).toBe('Unauthorized');
      expect(responseBody.message).toBe('Invalid code');
    });

    it('should return 500 when WordPress call throws an error', async () => {
      const req = new Request('http://localhost:3000/api/hwp/token?code=test-code');
      
      // Mock fetch error
      (global.fetch as jest.Mock).mockRejectedValue(new Error('Network error'));
      
      const response = await tokenHandler(req);
      
      expect(response.status).toBe(500);
      const responseBody = await response.json();
      expect(responseBody.error).toBe('Internal Server Error');
    });

    it('should throw an error when secret key is not set', async () => {
      const req = new Request('http://localhost:3000/api/hwp/token?code=test-code');
      
      // Mock missing secret key
      (getWpSecret as jest.Mock).mockReturnValue('');
      
      const response = await tokenHandler(req);
      
      expect(response.status).toBe(500);
      const responseBody = await response.json();
      expect(responseBody.error).toBe('Internal Server Error');
      expect(responseBody.message).toBe('WORDPRESS_SECRET_KEY must be set');
    });

    it('should delete refresh token cookie when WordPress returns an authentication error', async () => {
      const req = new Request('http://localhost:3000/api/hwp/token');
      
      // Mock refresh token in cookie
      (mockCookieStore.get as jest.Mock).mockReturnValue({
        name: cookieName,
        value: 'expired-refresh-token',
      });
      
      // Mock WordPress error response
      (global.fetch as jest.Mock).mockResolvedValue({
        ok: false,
        status: 401,
        json: jest.fn().mockResolvedValue({
          message: 'Token expired',
        }),
      });
      
      await tokenHandler(req);
      
      // Check cookie was deleted
      expect(mockCookieStore.delete).toHaveBeenCalledWith(cookieName);
    });
  });
});


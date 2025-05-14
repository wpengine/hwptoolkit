import { cookies } from 'next/headers';
import { 
  setRefreshToken, 
  deleteRefreshToken, 
  getRefreshToken,
  isValidEmail,
  isString
} from '../index';
import { getWpUrl } from '../../../lib/config';

// Mock dependencies
jest.mock('next/headers');
jest.mock('../../../lib/config', () => ({
  getWpUrl: jest.fn().mockReturnValue('https://example.com'),
}));

describe('Authentication utilities', () => {
  const cookieName = 'https://example.com-rt';
  const mockCookieStore = cookies();
  
  beforeEach(() => {
    jest.clearAllMocks();
  });
  
  describe('setRefreshToken', () => {
    it('should set refresh token cookie with correct parameters', async () => {
      const refreshToken = 'test-refresh-token';
      const expirationTime = Date.now() + 86400000; // Now + 1 day in ms
      
      await setRefreshToken(refreshToken, expirationTime);
      
      expect(cookies).toHaveBeenCalled();
      expect(mockCookieStore.set).toHaveBeenCalledWith(
        cookieName,
        refreshToken,
        expect.objectContaining({
          httpOnly: true,
          path: '/',
          expires: expect.any(Date),
          sameSite: 'lax',
        })
      );
    });
    
    it('should use the correct expiration time', async () => {
      const refreshToken = 'test-refresh-token';
      const expirationTime = Date.now() + 86400000; // Now + 1 day in ms
      
      await setRefreshToken(refreshToken, expirationTime);
      
      // Extract the expires argument from the mock call
      const setCookieArgs = (mockCookieStore.set as jest.Mock).mock.calls[0][2];
      expect(setCookieArgs.expires.getTime()).toBe(new Date(expirationTime).getTime());
    });
  });
  
  describe('deleteRefreshToken', () => {
    it('should return true if cookie is found and deleted', async () => {
      // Mock that cookie exists
      (mockCookieStore.get as jest.Mock).mockReturnValueOnce({
        name: cookieName,
        value: 'test-refresh-token',
      });
      
      const result = await deleteRefreshToken();
      
      expect(cookies).toHaveBeenCalled();
      expect(mockCookieStore.delete).toHaveBeenCalledWith(cookieName);
      expect(result).toBe(true);
    });
    
    it('should return false if cookie is not found', async () => {
      // Mock that cookie does not exist
      (mockCookieStore.get as jest.Mock).mockReturnValueOnce(undefined);
      
      const result = await deleteRefreshToken();
      
      expect(cookies).toHaveBeenCalled();
      expect(mockCookieStore.delete).not.toHaveBeenCalled();
      expect(result).toBe(false);
    });
  });
  
  describe('getRefreshToken', () => {
    it('should return the token value if cookie is found', () => {
      // Mock that cookie exists
      (mockCookieStore.get as jest.Mock).mockReturnValueOnce({
        name: cookieName,
        value: 'test-refresh-token',
      });
      
      const result = getRefreshToken();
      
      expect(cookies).toHaveBeenCalled();
      expect(result).toBe('test-refresh-token');
    });
    
    it('should return null if cookie is not found', () => {
      // Mock that cookie does not exist
      (mockCookieStore.get as jest.Mock).mockReturnValueOnce(undefined);
      
      const result = getRefreshToken();
      
      expect(cookies).toHaveBeenCalled();
      expect(result).toBeNull();
    });
  });
  
  describe('isValidEmail', () => {
    it('should return true for valid email addresses', () => {
      expect(isValidEmail('user@example.com')).toBe(true);
      expect(isValidEmail('name.surname@example.co.uk')).toBe(true);
      expect(isValidEmail('user+tag@example.com')).toBe(true);
    });
    
    it('should return false for invalid email addresses', () => {
      expect(isValidEmail('user@')).toBe(false);
      expect(isValidEmail('user@example')).toBe(false);
      expect(isValidEmail('userexample.com')).toBe(false);
      expect(isValidEmail('@example.com')).toBe(false);
      expect(isValidEmail('user@.com')).toBe(false);
      expect(isValidEmail('')).toBe(false);
    });
  });
  
  describe('isString', () => {
    it('should return true for string values', () => {
      expect(isString('test')).toBe(true);
      expect(isString('')).toBe(true);
      expect(isString(String('test'))).toBe(true);
      expect(isString(new String('test'))).toBe(true);
    });
    
    it('should return false for non-string values', () => {
      expect(isString(123)).toBe(false);
      expect(isString(true)).toBe(false);
      expect(isString(null)).toBe(false);
      expect(isString(undefined)).toBe(false);
      expect(isString({})).toBe(false);
      expect(isString([])).toBe(false);
    });
  });
});


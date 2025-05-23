import { onLogin, validationError } from '../loginAction';
import { setRefreshToken } from '../utils';
import { getGraphqlEndpoint } from '../../lib/config';

// Mock dependencies
jest.mock('../utils', () => ({
  setRefreshToken: jest.fn(),
  isValidEmail: jest.fn((email) => email.includes('@')),
  isString: jest.fn((val) => typeof val === 'string'),
}));

jest.mock('../../lib/config', () => ({
  getGraphqlEndpoint: jest.fn().mockReturnValue('https://example.com/graphql'),
}));

// Mock global fetch
global.fetch = jest.fn();

describe('loginAction', () => {
  // Setup form data for tests
  let formData: FormData;
  
  beforeEach(() => {
    jest.clearAllMocks();
    
    // Create fresh FormData for each test
    formData = new FormData();
    formData.append('usernameEmail', 'testuser');
    formData.append('password', 'testpassword');
    
    // Default mock response for GraphQL
    (global.fetch as jest.Mock).mockImplementation(async (url, options) => {
      if (url === 'https://example.com/graphql') {
        return {
          ok: true,
          json: jest.fn().mockResolvedValue({
            data: {
              generateAuthorizationCode: {
                code: 'test-auth-code',
                error: null,
              },
            },
          }),
        };
      } else if (url.includes('/api/hwp/token')) {
        return {
          ok: true,
          json: jest.fn().mockResolvedValue({
            accessToken: 'test-access-token',
            accessTokenExpiration: 3600,
            refreshToken: 'test-refresh-token',
            refreshTokenExpiration: 86400,
          }),
        };
      }
      return { ok: false };
    });
  });
  
  describe('form validation', () => {
    it('should return validation error if usernameEmail is missing', async () => {
      formData.delete('usernameEmail');
      const result = await onLogin(formData);
      expect(result).toEqual(validationError);
    });
    
    it('should return validation error if password is missing', async () => {
      formData.delete('password');
      const result = await onLogin(formData);
      expect(result).toEqual(validationError);
    });
    
    it('should return validation error if usernameEmail is not a string', async () => {
      // Mock isString to return false for this test
      (require('../utils').isString as jest.Mock).mockReturnValueOnce(false);
      
      const result = await onLogin(formData);
      expect(result).toEqual(validationError);
    });
    
    it('should return validation error if password is not a string', async () => {
      // Mock isString to return true for username, false for password
      const isStringMock = require('../utils').isString as jest.Mock;
      isStringMock.mockReturnValueOnce(true).mockReturnValueOnce(false);
      
      const result = await onLogin(formData);
      expect(result).toEqual(validationError);
    });
  });
  
  describe('WordPress GraphQL integration', () => {
    it('should send email when input is a valid email', async () => {
      formData.set('usernameEmail', 'test@example.com');
      
      await onLogin(formData);
      
      expect(global.fetch).toHaveBeenCalledWith(
        'https://example.com/graphql',
        expect.objectContaining({
          body: expect.stringContaining('"email":"test@example.com"'),
        })
      );
    });
    
    it('should send username when input is not an email', async () => {
      formData.set('usernameEmail', 'testuser');
      
      await onLogin(formData);
      
      expect(global.fetch).toHaveBeenCalledWith(
        'https://example.com/graphql',
        expect.objectContaining({
          body: expect.stringContaining('"username":"testuser"'),
        })
      );
    });
    
    it('should handle GraphQL errors correctly', async () => {
      // Mock GraphQL error response
      (global.fetch as jest.Mock).mockImplementationOnce(() => ({
        ok: true,
        json: jest.fn().mockResolvedValue({
          data: {
            generateAuthorizationCode: {
              code: null,
              error: 'Invalid username or password',
            },
          },
        }),
      }));
      
      const result = await onLogin(formData);
      
      expect(result).toEqual({
        error: 'Invalid username or password',
      });
    });
    
    it('should handle network errors gracefully', async () => {
      // Mock network error
      (global.fetch as jest.Mock).mockRejectedValueOnce(new Error('Network error'));
      
      const result = await onLogin(formData);
      
      expect(result).toEqual({
        error: 'There was an error logging in the user',
      });
    });
  });
  
  describe('authentication flow', () => {
    it('should fetch tokens with the authorization code', async () => {
      await onLogin(formData);
      
      // Check that second fetch call is to token endpoint with the code
      expect(global.fetch).toHaveBeenCalledTimes(2);
      const tokenCall = (global.fetch as jest.Mock).mock.calls[1];
      expect(tokenCall[0]).toContain('/api/hwp/token?code=test-auth-code');
    });
    
    it('should store refresh token on successful login', async () => {
      await onLogin(formData);
      
      expect(setRefreshToken).toHaveBeenCalledWith(
        'test-refresh-token',
        86400 * 1000 // Converted to milliseconds
      );
    });
    
    it('should return success message on successful login', async () => {
      const result = await onLogin(formData);
      
      expect(result).toEqual({
        message: 'User was successfully logged in',
      });
    });
    
    it('should handle token fetch errors', async () => {
      // Mock authorization code success but token fetch failure
      (global.fetch as jest.Mock)
        .mockImplementationOnce(() => ({
          ok: true,
          json: jest.fn().mockResolvedValue({
            data: {
              generateAuthorizationCode: {
                code: 'test-auth-code',
                error: null,
              },
            },
          }),
        }))
        .mockImplementationOnce(() => ({
          ok: false,
          status: 401,
          json: jest.fn().mockResolvedValue({
            error: 'Invalid code',
          }),
        }));
      
      const result = await onLogin(formData);
      
      expect(result).toEqual({
        error: 'There was an error logging in the user',
      });
    });
  });
});


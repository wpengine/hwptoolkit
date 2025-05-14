import { onLogout } from '../logoutAction';
import { deleteRefreshToken } from '../utils';

// Mock dependencies
jest.mock('../utils', () => ({
  deleteRefreshToken: jest.fn(),
}));

describe('logoutAction', () => {
  beforeEach(() => {
    jest.clearAllMocks();
  });
  
  it('should call deleteRefreshToken', async () => {
    await onLogout();
    expect(deleteRefreshToken).toHaveBeenCalled();
  });
  
  it('should return true when token deletion is successful', async () => {
    (deleteRefreshToken as jest.Mock).mockResolvedValueOnce(true);
    const result = await onLogout();
    expect(result).toBe(true);
  });
  
  it('should return false when no token is found for deletion', async () => {
    (deleteRefreshToken as jest.Mock).mockResolvedValueOnce(false);
    const result = await onLogout();
    expect(result).toBe(false);
  });
  
  it('should propagate any errors from token deletion', async () => {
    const testError = new Error('Token deletion error');
    (deleteRefreshToken as jest.Mock).mockRejectedValueOnce(testError);
    
    await expect(onLogout()).rejects.toThrow('Token deletion error');
  });
});


// Mock Next.js navigation
jest.mock('next/navigation', () => ({
  notFound: jest.fn(),
}));

// Mock Next.js headers and cookies
jest.mock('next/headers', () => {
  const mockCookieStore = {
    get: jest.fn(),
    set: jest.fn(),
    delete: jest.fn(),
    toString: jest.fn().mockReturnValue(''),
  };
  
  return {
    cookies: jest.fn().mockReturnValue(mockCookieStore),
  };
});

// Mock Next.js server response
jest.mock('next/server', () => ({
  NextResponse: {
    json: jest.fn().mockImplementation((data, options) => ({
      cookies: {
        set: jest.fn(),
      },
      ...data,
    })),
  },
}));

// Reset all mocks before each test
beforeEach(() => {
  jest.clearAllMocks();
  
  // Reset environment variables
  process.env.WORDPRESS_URL = 'https://example.com';
  process.env.WORDPRESS_SECRET_KEY = 'test-secret-key';
  process.env.NEXT_PUBLIC_URL = 'http://localhost:3000';
  process.env.NODE_ENV = 'test';
});


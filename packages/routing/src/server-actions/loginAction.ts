'use server';

import { getGraphqlEndpoint } from '../lib/config';
import { AuthorizeResponse } from '../types';
import { isValidEmail, isString, setRefreshToken } from './utils';

/**
 * GraphQL mutation for generating an authorization code
 */
const GENERATE_AUTHORIZATION_CODE = `
  mutation GenerateAuthorizationCode(
    $email: String
    $username: String
    $password: String!
  ) {
    generateAuthorizationCode(
      input: { email: $email, username: $username, password: $password }
    ) {
      code
      error
    }
  }
`;

/**
 * Response type for the generateAuthorizationCode mutation
 */
export type GenerateAuthCodeResponse = {
  data?:
    | {
        generateAuthorizationCode: {
          code: string;
          error: null;
        };
      }
    | {
        generateAuthorizationCode: {
          code: null;
          error: string;
        };
      };
};

/**
 * Error returned when the login form has invalid inputs
 */
export const validationError = {
  error:
    'There were validation errors. Please ensure your login action has two inputs, "usernameEmail" and "password"',
};

/**
 * Fetch tokens from the WordPress site using an authorization code
 * 
 * @param code Authorization code from WordPress
 * @returns AuthorizeResponse|null Tokens or null if failed
 */
async function fetchTokens(code: string): Promise<AuthorizeResponse | null> {
  const baseUrl = process.env.NEXT_PUBLIC_URL || '';
  const url = `${baseUrl}/api/hwp/token?code=${encodeURIComponent(code)}`;
  
  try {
    const response = await fetch(url, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
      },
      cache: 'no-store',
    });

    if (!response.ok) {
      if (response.status === 401) {
        return null;
      }
      throw new Error('Invalid response from token endpoint');
    }

    return await response.json() as AuthorizeResponse;
  } catch (err) {
    console.error('Error fetching tokens:', err);
    return null;
  }
}

/**
 * Server action to handle user login
 * 
 * @param formData Form data containing usernameEmail and password
 * @returns Object with message on success or error on failure
 */
export async function onLogin(formData: FormData) {
  try {
    const usernameEmail = formData.get('usernameEmail');
    const password = formData.get('password');

    // Validate inputs
    if (
      !usernameEmail ||
      !isString(usernameEmail) ||
      !password ||
      !isString(password)
    ) {
      return validationError;
    }

    // Determine if the user is logging in with an email or username
    const mutationVariables: {
      username?: string;
      email?: string;
      password: string;
    } = { password };

    if (isValidEmail(usernameEmail)) {
      mutationVariables.email = usernameEmail;
    } else {
      mutationVariables.username = usernameEmail;
    }

    // Generate authorization code using GraphQL
    const response = await fetch(getGraphqlEndpoint(), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        query: GENERATE_AUTHORIZATION_CODE,
        variables: mutationVariables,
      }),
      cache: 'no-store',
    });

    const { data } = await response.json() as GenerateAuthCodeResponse;

    // Handle authentication errors
    if (data?.generateAuthorizationCode.error !== null) {
      return {
        error: data?.generateAuthorizationCode.error || 'Login failed',
      };
    }

    // Get the authorization code and fetch tokens
    const { code } = data.generateAuthorizationCode;
    const tokens = await fetchTokens(code);

    if (!tokens) {
      throw new Error('Could not fetch tokens');
    }

    // Store the refresh token in a secure cookie
    await setRefreshToken(
      tokens.refreshToken,
      tokens.refreshTokenExpiration * 1000, // Convert seconds to milliseconds
    );

    return {
      message: 'User was successfully logged in',
    };
  } catch (err) {
    console.error('User could not be logged in:', err);

    return {
      error: 'There was an error logging in the user',
    };
  }
}


import { Injectable, signal, computed } from '@angular/core';
import { HttpClient, HttpHeaders, HttpErrorResponse } from '@angular/common/http';
import { Observable, BehaviorSubject, throwError, from } from 'rxjs';
import { map, catchError, tap, shareReplay } from 'rxjs/operators';
import { environment } from '../../environments/environment';

/**
 * GraphQL Client Service for Angular
 * Provides methods for executing GraphQL queries and mutations
 */
@Injectable({
  providedIn: 'root'
})
export class GraphQLService {
  private wpUrl = environment.wordpressUrl || 'http://localhost:8892';
  private endpoint = `${this.wpUrl}/graphql`;

  private httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json'
    })
  };

  constructor(private http: HttpClient) {}

  /**
   * Execute a GraphQL query
   * @param query - The GraphQL query string
   * @param variables - Variables for the GraphQL query
   * @returns Observable with the GraphQL response
   */
  query<T = any>(query: string, variables: Record<string, any> = {}): Observable<T> {
    const body = JSON.stringify({ query, variables });

    // Debug: Log the raw request being sent
    console.log('🌐 GraphQL HTTP Request:', {
      endpoint: this.endpoint,
      method: 'POST',
      headers: this.httpOptions.headers,
      body: body
    });

    return this.http.post<GraphQLResponse<T>>(this.endpoint, body, this.httpOptions)
      .pipe(
        tap(response => {
          // Debug: Log the raw HTTP response
          console.log('📡 GraphQL HTTP Response:', response.data);
        }),
        map(response => {
          if (response.errors) {
            console.error('GraphQL returned errors:', response.errors);
            throw new Error(response.errors[0]?.message || 'GraphQL query failed');
          }
          return response.data;
        }), 
        catchError(this.handleError)
      );
  }

  /**
   * Execute a GraphQL query with caching
   * @param query - The GraphQL query string
   * @param variables - Variables for the GraphQL query
   * @param cacheTime - Cache time in milliseconds (default: 5 minutes)
   * @returns Observable with cached GraphQL response
   */
  cachedQuery<T = any>(
    query: string, 
    variables: Record<string, any> = {}, 
    cacheTime: number = 5 * 60 * 1000
  ): Observable<T> {
    return this.query<T>(query, variables).pipe(
      shareReplay({ bufferSize: 1, refCount: true })
    );
  }

  private handleError = (error: HttpErrorResponse) => {
    let errorMessage = 'An error occurred';

    if (error.error instanceof ErrorEvent) {
      // Client-side error
      errorMessage = `Error: ${error.error.message}`;
    } else {
      // Server-side error
      if (error.headers.get('content-type')?.includes('text/html')) {
        errorMessage = 'Received HTML response instead of JSON from GraphQL endpoint';
        console.error('HTML response received:', error.error);
      } else {
        errorMessage = `Error Code: ${error.status}\nMessage: ${error.message}`;
      }
    }

    console.error('GraphQL Service Error:', errorMessage);
    return throwError(() => new Error(errorMessage));
  };
}

/**
 * GraphQL State Management Service
 * Provides reactive state management for GraphQL queries
 */
@Injectable({
  providedIn: 'root'
})
export class GraphQLStateService { 
  constructor(private graphqlService: GraphQLService) {}

  /**
   * Create a reactive GraphQL query with loading states
   * @param query - The GraphQL query string
   * @param initialVariables - Initial variables for the query
   * @returns Object with reactive state signals
   */
  createQuery<T = any>(query: string, initialVariables: Record<string, any> = {}) {
    const data = signal<T | null>(null);
    const loading = signal<boolean>(false);
    const error = signal<Error | null>(null);
    const variables = signal<Record<string, any>>(initialVariables);
    const execute = (newVariables?: Record<string, any>) => {
      if (newVariables) {
        variables.set(newVariables);
      }

      loading.set(true);
      error.set(null);

      // Debug: Log the query being sent
      console.group('🔍 GraphQL Query Debug');
      console.log('📤 Query:', query);
      console.log('📋 Variables:', variables());
      console.log('🌐 Endpoint:', `${environment.wordpressUrl || 'http://localhost:8892'}/graphql`);
      console.groupEnd();

      return this.graphqlService.query<T>(query, variables()).pipe(
        tap({
          next: (result) => {
            // Debug: Log successful response
            console.group('✅ GraphQL Query Success');
            console.log('📥 Response Data:', result);
            console.log('🔧 Query was:', query.substring(0, 100) + (query.length > 100 ? '...' : ''));
            console.groupEnd();
            
            data.set(result);
            loading.set(false);
          },
          error: (err) => {
            // Debug: Log error response
            console.group('❌ GraphQL Query Error');
            console.error('🚨 Error:', err);
            console.log('🔧 Failed Query:', query.substring(0, 100) + (query.length > 100 ? '...' : ''));
            console.log('📋 Variables used:', variables());
            console.groupEnd();
            
            error.set(err);
            loading.set(false);
          }
        })
      );
    };

    // Auto-execute on creation
    execute().subscribe();

    return {
      data: data.asReadonly(),
      loading: loading.asReadonly(),
      error: error.asReadonly(),
      variables: variables.asReadonly(),
      refetch: execute,
      setVariables: (newVars: Record<string, any>) => {
        variables.set(newVars);
        return execute();
      }
    };
  }
}

/**
 * Standalone function for simple GraphQL queries
 * @param query - The GraphQL query string
 * @param variables - Variables for the query
 * @returns Promise with the GraphQL response
 */
export async function fetchGraphQL<T = any>(
  query: string, 
  variables: Record<string, any> = {}
): Promise<T> {
  const wpUrl = environment.wordpressUrl || 'http://localhost:8892';
  const endpoint = `${wpUrl}/graphql`;

  // Debug: Log the standalone fetch request
  console.group('🔍 Standalone GraphQL Fetch Debug');
  console.log('📤 Query:', query);
  console.log('📋 Variables:', variables);
  console.log('🌐 Endpoint:', endpoint);
  console.groupEnd();

  try {
    const response = await fetch(endpoint, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ query, variables }),
    });

    if (!response.ok) {
      throw new Error(`Network error: ${response.status} ${response.statusText}`);
    }

    const result: GraphQLResponse<T> = await response.json();

    // Debug: Log the successful response
    console.group('✅ Standalone GraphQL Fetch Success');
    console.log('📥 Response Data:', result);
    console.groupEnd();

    if (result.errors) {
      console.error('GraphQL Error:', result.errors);
      throw new Error(result.errors[0]?.message || 'Failed to fetch data from WordPress');
    }

    return result.data;
  } catch (error) {
    // Debug: Log the error
    console.group('❌ Standalone GraphQL Fetch Error');
    console.error('🚨 Error:', error);
    console.log('🔧 Failed Query:', query.substring(0, 100) + (query.length > 100 ? '...' : ''));
    console.log('📋 Variables used:', variables);
    console.groupEnd();
    
    console.error('Error fetching from WordPress:', error);
    throw error;
  }
}

/**
 * Standalone function for GraphQL mutations
 * @param mutation - The GraphQL mutation string
 * @param variables - Variables for the mutation
 * @returns Promise with the mutation result
 */
export async function executeMutation<T = any>(
  mutation: string, 
  variables: Record<string, any> = {}
): Promise<GraphQLMutationResult<T>> {
  const wpUrl = environment.wordpressUrl || 'http://localhost:8080';
  const endpoint = `${wpUrl}/graphql`;

  try {
    const response = await fetch(endpoint, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ query: mutation, variables }),
    });

    // Check if response is HTML (likely an error page)
    const contentType = response.headers.get('content-type');
    if (contentType && contentType.includes('text/html')) {
      console.error('Received HTML response instead of JSON');
      const htmlContent = await response.text();
      console.error('HTML response preview:', htmlContent.substring(0, 200));
      throw new Error('Received HTML response from GraphQL endpoint. Check server configuration.');
    }

    const responseText = await response.text();

    try {
      const result: GraphQLResponse<T> = JSON.parse(responseText);

      if (result.errors) {
        console.error('GraphQL returned errors:', result.errors);
      }

      return {
        data: result.data,
        errors: result.errors || null,
      };
    } catch (jsonError) {
      console.error('Failed to parse response as JSON:', jsonError);
      throw new Error(`Invalid JSON response: ${(jsonError as Error).message}`);
    }
  } catch (error) {
    console.error('Error executing GraphQL mutation:', error);
    return { data: null, errors: [error as Error] };
  }
}

/**
 * Template literal tag for GraphQL queries
 * @param strings - Template literal strings
 * @param values - Template literal values
 * @returns Formatted GraphQL query string
 */
export function gql(strings: TemplateStringsArray, ...values: any[]): string {
  return strings.reduce((result, string, i) => {
    return result + string + (values[i] || '');
  }, '');
}

// Type definitions
interface GraphQLResponse<T = any> {
  data: T;
  errors?: GraphQLError[];
}

interface GraphQLError {
  message: string;
  locations?: Array<{ line: number; column: number }>;
  path?: string[];
  extensions?: Record<string, any>;
}

interface GraphQLMutationResult<T = any> {
  data: T | null;
  errors: GraphQLError[] | Error[] | null;
}

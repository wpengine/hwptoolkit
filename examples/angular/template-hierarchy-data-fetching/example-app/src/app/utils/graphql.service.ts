import { Injectable } from '@angular/core';
import {
  HttpClient,
  HttpHeaders,
  HttpErrorResponse,
} from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { map, catchError, tap } from 'rxjs/operators';
import { environment } from '../../environments/environment';

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

/**
 * GraphQL fetching using HTTP client
 * Uses Angular HttpClient for reactive queries.
 * Used for fetching data in - load more posts in Blogs and load more Comments.
 */
@Injectable({
  providedIn: 'root',
})
export class GraphQLService {
  private wpUrl = environment.wordpressUrl || 'http://localhost:8892';
  private endpoint = `${this.wpUrl}/graphql`;

  private httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json',
    }),
  };

  constructor(private http: HttpClient) {}

  /**
   * Execute a GraphQL query for CSR
   * @param query - The GraphQL query string
   * @param variables - Variables for the GraphQL query
   * @returns Observable with the GraphQL response
   */
  query<T = any>(
    query: string,
    variables: Record<string, any> = {}
  ): Observable<T> {
    const body = JSON.stringify({ query, variables });

    //console.log('🌐 CSR GraphQL Request:', {
    //   endpoint: this.endpoint,
    //   variables,
    // });

    return this.http
      .post<GraphQLResponse<T>>(this.endpoint, body, this.httpOptions)
      .pipe(
        tap((response) => {
          //console.log('📡 HTTP GraphQL Response:', response.data);
        }),
        map((response) => {
          if (response.errors) {
            throw new Error(
              response.errors[0]?.message || 'GraphQL query failed'
            );
          }
          return response.data;
        }),
        catchError(this.handleError)
      );
  }

  private handleError = (error: HttpErrorResponse) => {
    let errorMessage = 'An error occurred';

    if (error.error instanceof ErrorEvent) {
      errorMessage = `Error: ${error.error.message}`;
    } else {
      if (error.headers.get('content-type')?.includes('text/html')) {
        errorMessage =
          'Received HTML response instead of JSON from GraphQL endpoint';
      } else {
        errorMessage = `Error Code: ${error.status}\nMessage: ${error.message}`;
      }
    }
    return throwError(() => new Error(errorMessage));
  };
}

/**
 * Standard GraphQL Fetch Function
 */
export async function fetchGraphQL<T = any>(
  query: string,
  variables: Record<string, any> = {}
): Promise<T> {
  const wpUrl = environment.wordpressUrl || 'http://localhost:8892';
  const endpoint = `${wpUrl}/graphql`;

  //console.log('🔍 GraphQL Request:', { endpoint, variables });

  try {
    const response = await fetch(endpoint, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ query, variables }),
    });

    if (!response.ok) {
      throw new Error(
        `Network error: ${response.status} ${response.statusText}`
      );
    }

    const result: GraphQLResponse<T> = await response.json();

    //console.log('✅ GraphQL Response:', result.data);

    if (result.errors) {
      throw new Error(
        result.errors[0]?.message || 'Failed to fetch data from WordPress'
      );
    }

    return result.data;
  } catch (error) {
    //console.error('❌ GraphQL Error:', error);
    throw error;
  }
}

/**
 * GraphQL Mutation Function
 * Used for submitting forms, comments, etc.
 */
export async function executeMutation<T = any>(
  mutation: string,
  variables: Record<string, any> = {}
): Promise<GraphQLMutationResult<T>> {
  const wpUrl = environment.wordpressUrl || 'http://localhost:8892';
  const endpoint = `${wpUrl}/graphql`;

  //console.log('🚀 Mutation Request:', { endpoint, variables });

  try {
    const response = await fetch(endpoint, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ query: mutation, variables }),
    });

    const contentType = response.headers.get('content-type');
    if (contentType && contentType.includes('text/html')) {
      throw new Error(
        'Received HTML response from GraphQL endpoint. Check server configuration.'
      );
    }

    const result: GraphQLResponse<T> = await response.json();

    //console.log('✅ Mutation Response:', result);

    return {
      data: result.data,
      errors: result.errors || null,
    };
  } catch (error) {
    //console.error('❌ Mutation Error:', error);
    return { data: null, errors: [error as Error] };
  }
}

/**
 * Template literal tag for GraphQL queries
 */
export function gql(strings: TemplateStringsArray, ...values: any[]): string {
  return strings.reduce((result, string, i) => {
    return result + string + (values[i] || '');
  }, '');
}

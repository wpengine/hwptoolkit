import { Injectable } from '@angular/core';
import { HttpClient, HttpErrorResponse } from '@angular/common/http';
import { firstValueFrom, throwError, Subject } from 'rxjs';
import { catchError, takeUntil } from 'rxjs/operators';
import { environment } from '../../environments/environment';
import {
  getTemplate,
  getPossibleTemplates,
  type WordPressTemplate,
} from './templates';
import { SEED_QUERY } from './seedQuery';
import { TemplateDiscoveryService } from '../services/template-discovery.service';

export type TemplateData = {
  uri: string;
  seedQuery: any;
  availableTemplates: Array<{ id: string; path: string }>;
  possibleTemplates: string[];
  template: WordPressTemplate;
};

export interface GraphQLResponse<T = any> {
  data: T;
  errors?: Array<{
    message: string;
    locations?: Array<{ line: number; column: number }>;
    path?: string[];
  }>;
}

@Injectable({
  providedIn: 'root',
})
export class TemplateHierarchyService {
  private wpUrl = environment.wordpressUrl || 'http://localhost:8892';
  private graphqlEndpoint = `${this.wpUrl}/graphql`;
  private cancelRequests$ = new Subject<void>(); // Add cancellation subject

  constructor(
    private http: HttpClient,
    private templateDiscoveryService: TemplateDiscoveryService,
  ) {}

  /**
   * Cancel all pending requests from this service
   */
  cancelAllRequests(): void {
    console.log('🚫 Cancelling all TemplateHierarchyService requests');
    this.cancelRequests$.next();
  }

  async uriToTemplate({
    uri,
    cancelToken,
  }: {
    uri: string;
    cancelToken?: Subject<void>;
  }): Promise<TemplateData> {
    try {
      console.group('🎯 Template Hierarchy Resolution');
      console.log('📍 URI:', uri);
      console.log('🌐 GraphQL Endpoint:', this.graphqlEndpoint);

      // Use the provided cancel token or the service-level one
      const cancellation$ = cancelToken || this.cancelRequests$;

      const seedQueryResponse = await this.fetchSeedQuery(uri, cancellation$);

      // Check if cancelled before proceeding
      if (cancellation$.closed) {
        console.log('🚫 Operation cancelled after seed query');
        throw new Error('Operation cancelled');
      }

      if (!seedQueryResponse.data?.nodeByUri) {
        console.error('❌ HTTP/404 - Not Found in WordPress:', uri);
        throw new Error(`URI not found in WordPress: ${uri}`);
      }

      const availableTemplates =
        await this.fetchAvailableTemplates(cancellation$);

      // Check if cancelled before proceeding
      if (cancellation$.closed) {
        console.log('🚫 Operation cancelled after template fetch');
        throw new Error('Operation cancelled');
      }

      if (!availableTemplates || availableTemplates.length === 0) {
        console.error('❌ No templates found');
        throw new Error('No available templates');
      }

      const possibleTemplates = getPossibleTemplates(
        seedQueryResponse.data.nodeByUri,
      );

      if (!possibleTemplates || possibleTemplates.length === 0) {
        console.error('❌ No possible templates found for content type');
        throw new Error('No possible templates for this URI');
      }

      const template = getTemplate(availableTemplates, possibleTemplates);

      if (!template) {
        console.error('❌ No template found for route');
        throw new Error('No template found for this URI');
      }

      console.log('✅ Template resolved:', template);
      console.log('📋 Possible templates:', possibleTemplates);
      console.groupEnd();

      return {
        uri,
        seedQuery: seedQueryResponse.data,
        availableTemplates,
        possibleTemplates,
        template,
      };
    } catch (error) {
      console.groupEnd();
      console.error('❌ Template hierarchy resolution failed:', error);
      throw error;
    }
  }

  private async fetchSeedQuery(
    uri: string,
    cancelToken: Subject<void>,
  ): Promise<GraphQLResponse> {
    const body = {
      query: SEED_QUERY,
      variables: { uri },
    };

    console.log('📤 Fetching seed query for URI:', uri);

    try {
      const response = await firstValueFrom(
        this.http
          .post<GraphQLResponse>(this.graphqlEndpoint, body, {
            headers: {
              'Content-Type': 'application/json',
            },
          })
          .pipe(
            takeUntil(cancelToken), // ✅ Add cancellation here
            catchError((error: HttpErrorResponse) => {
              // Check if it's a cancellation
              if (cancelToken.closed) {
                console.log('🚫 HTTP request cancelled');
                return throwError(() => new Error('Request cancelled'));
              }
              console.error('Error in GraphQL HTTP request:', error);
              return throwError(
                () =>
                  new Error(`GraphQL HTTP request failed: ${error.message}`),
              );
            }),
          ),
      );

      if (response.errors && response.errors.length > 0) {
        console.error('GraphQL errors:', response.errors);
        throw new Error(`GraphQL error: ${response.errors[0].message}`);
      }

      return response;
    } catch (error: any) {
      console.error('Error fetching seed query (after pipe):', error);
      throw new Error(`Failed to fetch seed query: ${error.message || error}`);
    }
  }

  private async fetchAvailableTemplates(
    cancelToken: Subject<void>,
  ): Promise<Array<{ id: string; path: string }>> {
    console.log(
      '📤 Fetching available templates from TemplateDiscoveryService',
    );
    try {
      const templates = await firstValueFrom(
        this.templateDiscoveryService.getAvailableTemplates().pipe(
          takeUntil(cancelToken), // ✅ Add cancellation here
          catchError((error: HttpErrorResponse) => {
            // Check if it's a cancellation
            if (cancelToken.closed) {
              console.log('🚫 Template discovery request cancelled');
              return throwError(() => new Error('Request cancelled'));
            }
            console.error(
              'Error in TemplateDiscoveryService HTTP request:',
              error,
            );
            return throwError(
              () =>
                new Error(
                  `Template discovery HTTP request failed: ${error.message}`,
                ),
            );
          }),
        ),
      );
      console.log('📥 Available templates:', templates);
      return templates;
    } catch (error: any) {
      console.error(
        'Error fetching available templates from backend (after pipe):',
        error,
      );
      console.log('🔄 Using fallback templates');

      // Return fallback templates instead of throwing if cancelled
      if (error.message === 'Request cancelled') {
        throw error;
      }

      return this.getDefaultTemplates();
    }
  }

  private getDefaultTemplates(): Array<{ id: string; path: string }> {
    return [
      { id: 'index', path: '/src/app/components/wp-templates/index' },
      { id: 'single', path: '/src/app/components/wp-templates/single' },
      { id: 'page', path: '/src/app/components/wp-templates/page' },
      { id: 'singular', path: '/src/app/components/wp-templates/singular' },
      { id: 'archive', path: '/src/app/components/wp-templates/archive' },
      { id: 'front-page', path: '/src/app/components/wp-templates/front-page' },
      { id: 'home', path: '/src/app/components/wp-templates/home' },
    ];
  }

  async checkUriExists(uri: string): Promise<boolean> {
    try {
      const response = await this.fetchSeedQuery(uri, new Subject<void>());
      return !!response.data?.nodeByUri;
    } catch {
      return false;
    }
  }

  async getTemplateSuggestions(uri: string): Promise<string[]> {
    try {
      const response = await this.fetchSeedQuery(uri, new Subject<void>());
      if (response.data?.nodeByUri) {
        return getPossibleTemplates(response.data.nodeByUri);
      }
      return [];
    } catch {
      return [];
    }
  }
}

export async function uriToTemplate({
  uri,
}: {
  uri: string;
}): Promise<TemplateData> {
  console.warn(
    '⚠️ uriToTemplate function is deprecated. Use TemplateHierarchyService instead.',
  );
  throw new Error(
    'This function requires dependency injection. Use TemplateHierarchyService instead.',
  );
}

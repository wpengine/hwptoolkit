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
    private templateDiscoveryService: TemplateDiscoveryService
  ) {}

  /**
   * Cancel all pending requests from this service
   */
  cancelAllRequests(): void {
    //console.log('🚫 Cancelling all TemplateHierarchyService requests');
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
      //console.log('📍 URI:', uri);
      //console.log('🌐 GraphQL Endpoint:', this.graphqlEndpoint);

      // Use the provided cancel token or the service-level one
      const cancellation$ = cancelToken || this.cancelRequests$;

      const seedQueryResponse = await this.fetchSeedQuery(uri, cancellation$);

      // Check if cancelled before proceeding
      if (cancellation$.closed) {
        throw new Error('Operation cancelled');
      }

      if (!seedQueryResponse.data?.nodeByUri) {
        throw new Error(`URI not found in WordPress: ${uri}`);
      }

      const availableTemplates =
        await this.fetchAvailableTemplates(cancellation$);

      if (cancellation$.closed) {
        throw new Error('Operation cancelled');
      }

      if (!availableTemplates || availableTemplates.length === 0) {
        throw new Error('No available templates');
      }

      const possibleTemplates = getPossibleTemplates(
        seedQueryResponse.data.nodeByUri
      );

      if (!possibleTemplates || possibleTemplates.length === 0) {
        throw new Error('No possible templates for this URI');
      }

      const template = getTemplate(availableTemplates, possibleTemplates);

      if (!template) {
        throw new Error('No template found for this URI');
      }

      //console.log('✅ Template resolved:', template);
      //console.log('📋 Possible templates:', possibleTemplates);
      //console.groupEnd();

      return {
        uri,
        seedQuery: seedQueryResponse.data,
        availableTemplates,
        possibleTemplates,
        template,
      };
    } catch (error) {
      //console.groupEnd();
      throw error;
    }
  }

  private async fetchSeedQuery(
    uri: string,
    cancelToken: Subject<void>
  ): Promise<GraphQLResponse> {
    const body = {
      query: SEED_QUERY,
      variables: { uri },
    };

    //console.log('📤 Fetching seed query for URI:', uri);

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
                return throwError(() => new Error('Request cancelled'));
              }
              return throwError(
                () => new Error(`GraphQL HTTP request failed: ${error.message}`)
              );
            })
          )
      );

      if (response.errors && response.errors.length > 0) {
        throw new Error(`GraphQL error: ${response.errors[0].message}`);
      }

      return response;
    } catch (error: any) {
      throw new Error(`Failed to fetch seed query: ${error.message || error}`);
    }
  }

  private async fetchAvailableTemplates(
    cancelToken: Subject<void>
  ): Promise<Array<{ id: string; path: string }>> {
    try {
      const templates = await firstValueFrom(
        this.templateDiscoveryService.getAvailableTemplates().pipe(
          takeUntil(cancelToken), // ✅ Add cancellation here
          catchError((error: HttpErrorResponse) => {
            // Check if it's a cancellation
            if (cancelToken.closed) {
              return throwError(() => new Error('Request cancelled'));
            }

            return throwError(
              () =>
                new Error(
                  `Template discovery HTTP request failed: ${error.message}`
                )
            );
          })
        )
      );
      //console.log('📥 Available templates:', templates);
      return templates;
    } catch (error: any) {
      throw error;
    }
  }
}
